<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Hotel;
use App\Models\Message;
use App\Models\Reservation;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class SupportCaseSeeder extends Seeder
{
    public function run(): void
    {
        $buyer = User::firstOrCreate(['email' => 'buyer@example.com'], [
            'name' => 'Olivia Jacobson',
            'password' => Hash::make('secret123'),
        ]);

        $seller = User::firstOrCreate(['email' => 'seller@example.com'], [
            'name' => 'Jamal Chatila',
            'password' => Hash::make('secret123'),
        ]);

        $hotel = Hotel::firstOrCreate([
            'vendor' => 'hotelbeds',
            'vendor_id' => 777001,
        ], [
            'name' => 'Intercontinental Phoenicia Beirut',
            'slug' => 'intercontinental-phoenicia-beirut',
            'status' => 'active',
            'country' => 'Lebanon',
            'city' => 'Beirut',
            'currency' => 'USD',
        ]);

        $reservation = Reservation::firstOrCreate([
            'confirmation_number' => 'SUP-TEST-001',
        ], [
            'hotel_id' => $hotel->id,
            'user_id' => $buyer->id,
            'guest_info' => [
                'holder' => [
                    'name' => 'Olivia',
                    'surname' => 'Jacobson',
                ],
                'rooms' => [],
            ],
            'total_price' => 110,
            'currency' => 'USD',
            'status' => 'confirmed',
            'check_in' => Carbon::now()->addDays(5)->toDateString(),
            'check_out' => Carbon::now()->addDays(7)->toDateString(),
            'customer_name' => $buyer->name,
            'customer_email' => $buyer->email,
        ]);

        $conversation = Conversation::create([
            'user_id' => $buyer->id,
            'type' => 'support',
            'subject' => $hotel->name,
            'last_message_at' => Carbon::now(),
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => 'Hello, I am having problems with my reservation. Could you please help me? Thanks.',
            'is_admin' => false,
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $seller->id,
            'body' => 'Dear Guest, we are trying to reach out to you regarding the reservation. If you need help, please respond.',
            'is_admin' => false,
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => 'I am having trouble check-in on arrival. Can you verify the booking?',
            'is_admin' => false,
        ]);

        SupportCase::create([
            'reservation_id' => $reservation->id,
            'hotel_id' => $hotel->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'conversation_id' => $conversation->id,
            'purchase_price' => 110,
            'currency' => 'USD',
            'bookings_24h' => 54,
            'status' => 'open',
            'decision' => null,
            'seller_responded' => true,
            'buyer_responded' => true,
        ]);

        $solvedReservation = Reservation::firstOrCreate([
            'confirmation_number' => 'SUP-TEST-002',
        ], [
            'hotel_id' => $hotel->id,
            'user_id' => $buyer->id,
            'guest_info' => [
                'holder' => [
                    'name' => 'Olivia',
                    'surname' => 'Jacobson',
                ],
                'rooms' => [],
            ],
            'total_price' => 110,
            'currency' => 'USD',
            'status' => 'confirmed',
            'check_in' => Carbon::now()->addDays(10)->toDateString(),
            'check_out' => Carbon::now()->addDays(12)->toDateString(),
            'customer_name' => $buyer->name,
            'customer_email' => $buyer->email,
        ]);

        SupportCase::create([
            'reservation_id' => $solvedReservation->id,
            'hotel_id' => $hotel->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'purchase_price' => 110,
            'currency' => 'USD',
            'bookings_24h' => 30,
            'status' => 'solved',
            'decision' => 'payout_continue',
            'seller_responded' => true,
            'buyer_responded' => true,
        ]);
    }
}
