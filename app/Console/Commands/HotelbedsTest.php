<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HotelbedsService;

class HotelbedsTest extends Command
{
    protected $signature = 'hotelbeds:test
                            {hotelId? : Single Hotelbeds hotel id/code}
                            {--hotelIds= : Comma-separated hotel ids (overrides hotelId)}
                            {--destination= : Destination code (use instead of hotelIds)}
                            {--checkIn= : check-in YYYY-MM-DD}
                            {--checkOut= : check-out YYYY-MM-DD}';

    protected $description = 'Test Hotelbeds availability for a hotel or destination and print rateKeys (sandbox)';

    public function handle(HotelbedsService $hb)
    {
        $hotelIdArg = $this->argument('hotelId');
        $hotelIdsOpt = $this->option('hotelIds');
        $destination = $this->option('destination');

        $checkIn = $this->option('checkIn') ?: now()->addDays(7)->toDateString();
        $checkOut = $this->option('checkOut') ?: now()->addDays(10)->toDateString();

        $payload = [
            'stay' => ['checkIn' => $checkIn, 'checkOut' => $checkOut],
            'occupancies' => [
                ['rooms' => 1, 'adults' => 2, 'children' => 0] // children mandatory
            ]
        ];

        // Build filter: exactly one of hotelIds OR destination
        if ($hotelIdsOpt) {
            $ids = array_filter(array_map('trim', explode(',', $hotelIdsOpt)));
            if (count($ids) === 0) {
                $this->error('Provided --hotelIds is empty.');
                return 1;
            }
            $payload['hotelIds'] = $ids;
            $this->info('Using hotelIds: ' . implode(', ', $ids));
        } elseif ($destination) {
            $payload['destination'] = ['code' => $destination];
            $this->info('Using destination: ' . $destination);
        } elseif ($hotelIdArg) {
            $payload['hotelIds'] = [$hotelIdArg];
            $this->info('Using hotelId arg: ' . $hotelIdArg);
        } else {
            $this->error('You must pass either --hotelIds or --destination or hotelId argument.');
            $this->line('Examples: php artisan hotelbeds:test 1234');
            $this->line('          php artisan hotelbeds:test --hotelIds=1234,5678');
            $this->line('          php artisan hotelbeds:test --destination=LEB');
            return 1;
        }

        $this->info("Request payload:\n" . json_encode($payload, JSON_PRETTY_PRINT));

        $resp = $hb->availability($payload);

        // Save RAW response for debugging
        file_put_contents(storage_path('logs/hotelbeds_test_raw.json'), json_encode($resp, JSON_PRETTY_PRINT));
        $this->info("Raw response saved to storage/logs/hotelbeds_test_raw.json");

        if (!is_array($resp)) {
            $this->error("Invalid response (not an array). Full raw saved to log.");
            return 1;
        }

        // Try to extract hotels + ratekeys robustly
        $hotels = $resp['hotels'] ?? $resp['hotelsList'] ?? $resp['hotelsResponse'] ?? [];

        if (empty($hotels)) {
            $this->warn("No hotels found in supplier response. Inspect storage/logs/hotelbeds_test_raw.json");
            return 0;
        }

        foreach ($hotels as $h) {
            $name = $h['name'] ?? $h['hotelName'] ?? 'UNKNOWN HOTEL';
            $this->line("Hotel: " . $name);

            $rooms = $h['rooms'] ?? $h['room'] ?? [];
            foreach ($rooms as $room) {
                $this->line("  Room: " . ($room['name'] ?? $room['roomTypeName'] ?? 'No Name'));

                $rates = $room['rates'] ?? $room['rate'] ?? [];
                foreach ($rates as $rt) {
                    $rateKey = $rt['rateKey'] ?? null;
                    $price = $rt['net'] ?? ($rt['price'] ?? null);
                    $currency = $rt['currency'] ?? $rt['netCurrency'] ?? '';
                    $this->line("    rateKey: " . ($rateKey ?? 'n/a') . "    price: " . ($price ?? 'n/a') . " " . $currency);
                }
            }
        }

        return 0;
    }
}
