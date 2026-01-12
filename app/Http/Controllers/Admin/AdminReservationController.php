<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Services\HotelbedsService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;


class AdminReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with('hotel');

        // Supplier filter
        if ($request->filled('supplier')) {
            $query->whereHas('hotel', function ($q) use ($request) {
                $q->where('vendor', $request->supplier);
            });
        }

        // Destination filter (destinationCode from guest_info JSON)
        if ($request->filled('destination')) {
            $query->where('guest_info->cityCode', $request->destination);
        }

        // Payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Date range filter (check_in)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('check_in', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Search (guest name, reservation number, supplier reference)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('confirmation_number', 'LIKE', "%{$search}%")
                  ->orWhere('supplier_reference', 'LIKE', "%{$search}%");
            });
        }

        $reservations = $query->orderByDesc('id')->paginate(20);

        return view('admin.reservations.index', compact('reservations'));
    }

    public function show(Reservation $reservation)
    {
        $reservation->load('hotel');

        $html = view('admin.reservations._detail_view', compact('reservation'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html
        ]);
    }

    //Used when: status = failed / failed_booking
    public function retryBooking(Reservation $reservation, HotelbedsService $hb)
    {
        if (!in_array($reservation->status, ['failed', 'failed_booking'])) {
            return back()->with('error', 'Retry not allowed for this booking.');
        }

        $guest = $reservation->guest_info;

        $payload = [
            'holder' => [
                'name'    => $guest['holder']['name'] ?? '',
                'surname' => $guest['holder']['surname'] ?? '',
            ],
            'rooms'            => $guest['rooms'],
            'clientReference'  => $guest['client_reference'],
            'remark'           => $guest['remark'] ?? null,
        ];

        $resp = $hb->book($payload);

        if (isset($resp['error'])) {
            Log::error("Retry booking failed", ['id' => $reservation->id, 'error' => $resp]);
            return $this->ajaxError(
                'Retry failed: '.($resp['error']['message'] ?? 'Unknown error')
            );

        }

        $booking = Arr::get($resp, 'booking');

        $reservation->update([
            'status'              => 'confirmed',
            'supplier_reference'  => Arr::get($booking, 'reference'),
            'confirmation_number' => Arr::get($booking, 'reference'),
            'raw_response'        => $booking,
        ]);

        return $this->ajaxSuccess(
            'Booking successfully retried.',
            'confirmed'
        );

    }

    // Used when: status = pending
    // Admin wants to manually fetch supplier status.

    public function checkStatus(Reservation $reservation, HotelbedsService $hb)
    {
        $status = strtolower($reservation->status);

        if (!in_array($status, ['pending', 'pending_booking', 'pending_payment'])) {
            return $this->ajaxError('Status check allowed only for pending reservations.');
        }


        if (!$reservation->supplier_reference) {
            return $this->ajaxError('No supplier reference available to check status.');
        }

        $resp = $hb->getBooking($reservation->supplier_reference);

        if (isset($resp['error'])) {
            return $this->ajaxError('Failed to fetch supplier status.');
        }

        $supplierStatus = strtoupper(Arr::get($resp, 'booking.status', 'PENDING'));

        $localStatus = match ($supplierStatus) {
            'CONFIRMED' => 'confirmed',
            'CANCELLED' => 'cancelled',
            default     => 'pending',
        };

        $reservation->update([
            'status'       => $localStatus,
            'raw_response' => $resp,
        ]);

        return $this->ajaxSuccess(
            'Status updated successfully.',
            $localStatus
        );
    }

    // Cancel Booking
    // Used when: status = confirmed

    public function cancelBooking(Reservation $reservation, HotelbedsService $hb)
    {
        if ($reservation->status !== 'confirmed') {
            return $this->ajaxError('Only confirmed bookings can be cancelled.');
        }

        if (!$reservation->supplier_reference) {
            return $this->ajaxError('Cannot cancel: missing supplier reference.');
        }

        $resp = $hb->cancelBooking(
            $reservation->supplier_reference,
            'CANCELLATION',
            'ENG'
        );

        if (isset($resp['error'])) {
            return $this->ajaxError('Supplier cancellation failed.');
        }

        $reservation->update([
            'status'       => 'cancelled',
            'raw_response' => $resp,
        ]);

        return $this->ajaxSuccess(
            'Booking cancelled successfully.',
            'cancelled'
        );
    }

    // Used when: status = modified

    public function rebook(Reservation $reservation, HotelbedsService $hb)
    {
        if ($reservation->status !== 'modified') {
            return $this->ajaxError('Rebook is only allowed for modified reservations.');
        }

        $guest = $reservation->guest_info;

        $payload = [
            'holder' => [
                'name'    => $guest['holder']['name'],
                'surname' => $guest['holder']['surname'],
            ],
            'rooms'           => $guest['rooms'],
            'clientReference' => $guest['client_reference'] ?? ('RB-' . now()->format('YmdHis')),
            'remark'          => 'Rebook after modification',
        ];

        $resp = $hb->book($payload);

        if (isset($resp['error'])) {
            return $this->ajaxError(
                'Rebooking failed: '.($resp['error']['message'] ?? 'Unknown')
            );
        }

        $booking = Arr::get($resp, 'booking');

        $reservation->update([
            'status'              => 'confirmed',
            'supplier_reference'  => $booking['reference'],
            'confirmation_number' => $booking['reference'],
            'raw_response'        => $booking,
        ]);

        return $this->ajaxSuccess(
            'Booking rebooked successfully.',
            'confirmed'
        );
    }


    public function failed(Request $request)
    {
        $query = Reservation::with('hotel')->failed();

        // Supplier filter
        if ($request->filled('supplier')) {
            $query->whereHas('hotel', function ($q) use ($request) {
                $q->where('vendor', $request->supplier);
            });
        }

        // Destination filter
        if ($request->filled('destination')) {
            $query->where('guest_info->cityCode', $request->destination);
        }

        // Date range filter (check_in)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('check_in', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%{$search}%")
                ->orWhere('confirmation_number', 'LIKE', "%{$search}%")
                ->orWhere('supplier_reference', 'LIKE', "%{$search}%");
            });
        }

        $reservations = $query->orderByDesc('id')->paginate(20);

        return view('admin.reservations.failed', compact('reservations'));
    }

    private function ajaxSuccess(string $message, string $newStatus = null)
{
    return response()->json([
        'success'     => true,
        'message'     => $message,
        'new_status'  => $newStatus,
    ]);
}

private function ajaxError(string $message)
{
    return response()->json([
        'success' => false,
        'message' => $message,
    ], 422);
}



}
