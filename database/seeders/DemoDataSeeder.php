<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Hotel;
use App\Models\MarketplaceResale;
use App\Models\Message;
use App\Models\PaymentMethod;
use App\Models\PromoClickEvent;
use App\Models\PromoEngineSetting;
use App\Models\PromoImpressionEvent;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\SearchLog;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
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

        $agent = User::firstOrCreate(['email' => 'agent@example.com'], [
            'name' => 'Support Agent',
            'password' => Hash::make('secret123'),
        ]);

        if (class_exists(Role::class)) {
            $buyer->assignRole('customer');
            $seller->assignRole('customer');
            $agent->assignRole('agent');
        }

        $hotel1 = Hotel::firstOrCreate([
            'vendor' => 'hotelbeds',
            'vendor_id' => 100001,
        ], [
            'name' => 'Intercontinental Phoenicia Beirut',
            'slug' => 'intercontinental-phoenicia-beirut',
            'status' => 'active',
            'country' => 'Lebanon',
            'city' => 'Beirut',
            'currency' => 'USD',
            'address' => 'Minet El Hosn, Beirut',
            'latitude' => 33.9004,
            'longitude' => 35.5097,
        ]);

        $hotel2 = Hotel::firstOrCreate([
            'vendor' => 'hotelbeds',
            'vendor_id' => 100002,
        ], [
            'name' => 'Hotel Arts Barcelona',
            'slug' => 'hotel-arts-barcelona',
            'status' => 'active',
            'country' => 'Spain',
            'city' => 'Barcelona',
            'currency' => 'EUR',
            'address' => 'Marina 19-21, Barcelona',
            'latitude' => 41.3851,
            'longitude' => 2.1734,
        ]);

        $room1 = Room::firstOrCreate([
            'hotel_id' => $hotel1->id,
            'name' => 'Deluxe Sea View',
        ], [
            'vendor_room_id' => 'HB-ROOM-DELUXE',
            'price_per_night' => 110,
            'refundable' => true,
            'availability' => 5,
            'amenities' => ['WiFi', 'Breakfast', 'Air Conditioning'],
        ]);

        $room2 = Room::firstOrCreate([
            'hotel_id' => $hotel2->id,
            'name' => 'City Suite',
        ], [
            'vendor_room_id' => 'HB-ROOM-SUITE',
            'price_per_night' => 150,
            'refundable' => false,
            'availability' => 3,
            'amenities' => ['WiFi', 'Pool', 'Gym'],
        ]);

        $reservation1 = Reservation::firstOrCreate([
            'confirmation_number' => 'DEMO-RES-001',
        ], [
            'hotel_id' => $hotel1->id,
            'room_id' => $room1->id,
            'user_id' => $buyer->id,
            'guest_info' => [
                'holder' => ['name' => 'Olivia', 'surname' => 'Jacobson'],
                'country' => 'Lebanon',
                'city' => 'Beirut',
                'rooms' => [],
            ],
            'total_price' => 220,
            'markup_amount' => 20,
            'currency' => 'USD',
            'status' => 'confirmed',
            'check_in' => Carbon::now()->addDays(4)->toDateString(),
            'check_out' => Carbon::now()->addDays(6)->toDateString(),
            'customer_name' => $buyer->name,
            'customer_email' => $buyer->email,
            'booking_channel' => 'Website',
        ]);

        $reservation2 = Reservation::firstOrCreate([
            'confirmation_number' => 'DEMO-RES-002',
        ], [
            'hotel_id' => $hotel2->id,
            'room_id' => $room2->id,
            'user_id' => $buyer->id,
            'guest_info' => [
                'holder' => ['name' => 'Olivia', 'surname' => 'Jacobson'],
                'country' => 'Spain',
                'city' => 'Barcelona',
                'rooms' => [],
            ],
            'total_price' => 300,
            'markup_amount' => 30,
            'currency' => 'EUR',
            'status' => 'cancelled',
            'check_in' => Carbon::now()->addDays(10)->toDateString(),
            'check_out' => Carbon::now()->addDays(12)->toDateString(),
            'customer_name' => $buyer->name,
            'customer_email' => $buyer->email,
            'booking_channel' => 'Website',
        ]);

        PaymentMethod::firstOrCreate([
            'user_id' => $buyer->id,
            'last4' => '4242',
        ], [
            'brand' => 'visa',
            'expiry_month' => 12,
            'expiry_year' => (int) Carbon::now()->addYears(2)->format('Y'),
            'holder_name' => $buyer->name,
            'gateway' => 'stripe',
            'gateway_reference' => 'pm_demo_4242',
            'is_default' => true,
        ]);

        SearchLog::create([
            'user_id' => $buyer->id,
            'device_type' => 'web',
            'destination_country' => 'Lebanon',
            'destination_city' => 'Beirut',
            'check_in' => Carbon::now()->addDays(4)->toDateString(),
            'check_out' => Carbon::now()->addDays(6)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'success' => true,
            'response_ms' => 240,
            'meta' => ['source' => 'demo'],
        ]);

        $conversation = Conversation::create([
            'user_id' => $buyer->id,
            'type' => 'support',
            'subject' => $hotel1->name,
            'last_message_at' => Carbon::now(),
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => 'I am having trouble check-in on arrival.',
            'is_admin' => false,
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $agent->id,
            'body' => 'We are reviewing your case and will get back shortly.',
            'is_admin' => true,
        ]);

        SupportCase::firstOrCreate([
            'reservation_id' => $reservation1->id,
        ], [
            'hotel_id' => $hotel1->id,
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

        MarketplaceResale::firstOrCreate([
            'reservation_id' => $reservation1->id,
        ], [
            'seller_id' => $buyer->id,
            'status' => 'listed',
            'listed_price' => 180,
            'currency' => 'USD',
            'listed_at' => Carbon::now(),
        ]);

        PromoEngineSetting::firstOrCreate([]);

        PromoImpressionEvent::create([
            'hotel_id' => $hotel1->id,
            'user_id' => $buyer->id,
            'session_id' => 'demo-session',
            'request_id' => 'demo-request',
            'context' => ['page' => 'search'],
        ]);

        PromoClickEvent::create([
            'hotel_id' => $hotel1->id,
            'user_id' => $buyer->id,
            'session_id' => 'demo-session',
            'request_id' => 'demo-request',
            'context' => ['page' => 'search'],
        ]);
    }
}
