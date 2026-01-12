<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\SupplierResponse;
use App\Services\HotelbedsService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Event as StripeEvent;
use Stripe\Webhook as StripeWebhook;
use Stripe\Refund;
use App\Models\HealthEventLog;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = StripeWebhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook verification failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'invalid_signature'], 400);
        }

        switch ($event->type) {

            /**
             * ---------------------------------------------
             * PAYMENT SUCCEEDED
             * ---------------------------------------------
             */
            case 'payment_intent.succeeded':

                $intent = $event->data->object;

                $reservation = $this->findReservation($intent);

                if (!$reservation) {
                    Log::warning('Reservation not found for payment intent', [
                        'payment_intent_id' => $intent->id
                    ]);
                    break;
                }

                // Idempotency: do nothing if already confirmed / failed
                if (in_array($reservation->status, ['confirmed', 'failed_booking'])) {
                    break;
                }

                DB::transaction(function () use ($reservation) {
                    $reservation->payment_status = 'succeeded';
                    $reservation->save();
                });

                try {
                    $this->confirmHotelbedsBooking(
                        $reservation,
                        app(HotelbedsService::class)
                    );
                } catch (\Throwable $e) {

                    Log::error('Hotelbeds booking failed after Stripe success', [
                        'reservation_id' => $reservation->id,
                        'error'          => $e->getMessage(),
                    ]);

                    // 🔁 AUTO REFUND (CRITICAL)
                    try {
                        Refund::create([
                            'payment_intent' => $reservation->stripe_payment_intent_id,
                        ]);
                    } catch (\Throwable $refundError) {
                        Log::critical('Stripe refund failed', [
                            'reservation_id' => $reservation->id,
                            'error'          => $refundError->getMessage(),
                        ]);
                    }

                    DB::transaction(function () use ($reservation) {
                        $reservation->status         = 'failed_booking';
                        $reservation->payment_status = 'refunded';
                        $reservation->save();
                    });
                }

                break;

            /**
             * ---------------------------------------------
             * PAYMENT FAILED
             * ---------------------------------------------
             */
            case 'payment_intent.payment_failed':

                $intent = $event->data->object;
                $reservation = $this->findReservation($intent);

                if ($reservation) {
                    $reservation->update([
                        'payment_status' => 'failed',
                        'status'         => 'payment_failed',
                    ]);
                }

                break;
        }

        return response()->json(['received' => true]);
    }

    /**
     * ---------------------------------------------
     * Find reservation safely
     * ---------------------------------------------
     */
    protected function findReservation($intent): ?Reservation
    {
        $reservationId = $intent->metadata->reservation_id ?? null;

        if ($reservationId) {
            return Reservation::find($reservationId);
        }

        return Reservation::where(
            'stripe_payment_intent_id',
            $intent->id
        )->first();
    }

    /**
     * ---------------------------------------------
     * Confirm Hotelbeds Booking
     * ---------------------------------------------
     */
    protected function confirmHotelbedsBooking(
        Reservation $reservation,
        HotelbedsService $hb
    ): void {

        $guestInfo    = $reservation->guest_info ?? [];
        $roomsPayload = $guestInfo['rooms'] ?? [];
        $holder       = $guestInfo['holder'] ?? null;

        if (!$holder || empty($roomsPayload)) {
            throw new \RuntimeException(
                'Missing holder or rooms payload on reservation.'
            );
        }

        $clientRef = substr(
            $guestInfo['client_reference'] ?? ('TRA' . now()->format('ymdHis')),
            0,
            20
        );

        $bookingPayload = [
            'holder' => [
                'name'    => $holder['name'],
                'surname' => $holder['surname'],
            ],
            'rooms'           => $roomsPayload,
            'clientReference' => $clientRef,
        ];

        if (!empty($guestInfo['remark'])) {
            $bookingPayload['remark'] = $guestInfo['remark'];
        }

        // Call Hotelbeds
        $resp = $hb->book($bookingPayload);

        /* -----------------------------------------
        * CALL SUPPLIER + MEASURE TIME
        * ----------------------------------------- */
        $start = microtime(true);
        $resp  = $hb->book($bookingPayload);
        $ms    = (int) ((microtime(true) - $start) * 1000);

        SupplierResponse::create([
            'supplier'        => 'hotelbeds',
            'endpoint'        => '/hotel-api/1.0/bookings',
            'request_payload' => $bookingPayload,
            'response_body'   => json_encode($resp),
            'status_code'     => isset($resp['error']) ? 400 : 200,
        ]);

        if (isset($resp['error'])) {
            HealthEventLog::create([
                'event_date' => now()->toDateString(),
                'domain' => 'booking',
                'action' => 'book',
                'status' => 'failure',
                'response_time_ms' => $ms,
                'meta' => [
                    'reservation_id' => $reservation->id,
                    'supplier' => 'hotelbeds',
                    'error_code' => $resp['error']['code'] ?? null,
                    'error_message' => $resp['error']['message'] ?? null,
                ]
            ]);
            throw new \RuntimeException(
                'Hotelbeds book error: ' . json_encode($resp['error'])
            );
        }

        $booking = Arr::get($resp, 'booking');

        if (!$booking) {

            HealthEventLog::create([
                'event_date' => now()->toDateString(),
                'domain' => 'booking',
                'action' => 'book',
                'status' => 'failure',
                'response_time_ms' => $ms,
                'meta' => [
                    'reservation_id' => $reservation->id,
                    'reason' => 'missing_booking_object',
                ]
            ]);

            throw new \RuntimeException(
                'Hotelbeds booking missing booking object'
            );
        }

        DB::transaction(function () use ($reservation, $booking, $guestInfo, $ms) {

            $reservation->confirmation_number =
                Arr::get($booking, 'reference');

            $reservation->supplier_reference =
                Arr::get($booking, 'reference');

            $reservation->raw_response = $booking;

            $reservation->status = match (strtoupper(Arr::get($booking, 'status', 'CONFIRMED'))) {
                'CONFIRMED' => 'confirmed',
                'CANCELLED' => 'cancelled',
                default     => 'pending',
            };

            // Set check-in/out if missing
            if (!$reservation->check_in) {
                $reservation->check_in =
                    Arr::get($booking, 'hotel.checkIn');
            }

            if (!$reservation->check_out) {
                $reservation->check_out =
                    Arr::get($booking, 'hotel.checkOut');
            }

            $reservation->currency =
                Arr::get($booking, 'hotel.currency', $reservation->currency);

            $reservation->save();
                    
            HealthEventLog::create([
                'event_date' => now()->toDateString(),
                'domain' => 'booking',
                'action' => 'book',
                'status' => 'success',
                'response_time_ms' => $ms,
                'meta' => [
                    'reservation_id' => $reservation->id,
                    'supplier' => 'hotelbeds',
                    'reference' => Arr::get($booking, 'reference'),
                ]
            ]);
        });
    }
}
