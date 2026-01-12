<?php

namespace App\Jobs;

use App\Models\ApiHealthCheck;
use App\Services\HotelbedsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckApiHealthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(HotelbedsService $hotelbeds): void
    {
        $this->check('database', function () {
            DB::select('SELECT 1');
        });

        $this->check('hotelbeds_availability', function () use ($hotelbeds) {
            $hotelbeds->availability([
                'stay' => [
                    'checkIn' => now()->addDays(7)->toDateString(),
                    'checkOut' => now()->addDays(8)->toDateString(),
                ],
                'occupancies' => [[
                    'rooms' => 1,
                    'adults' => 1,
                    'children' => 0,
                ]],
                'destination' => ['code' => 'BCN'],
            ]);
        });
    }

    protected function check(string $service, callable $callback): void
    {
        $start = microtime(true);

        try {
            $callback();

            $time = (int)((microtime(true) - $start) * 1000);

            ApiHealthCheck::updateOrCreate(
                ['service_name' => $service],
                [
                    'status' => 'healthy',
                    'response_time_ms' => $time,
                    'error_message' => null,
                    'last_checked_at' => now(),
                ]
            );

        } catch (Throwable $e) {
            ApiHealthCheck::updateOrCreate(
                ['service_name' => $service],
                [
                    'status' => 'down',
                    'response_time_ms' => null,
                    'error_message' => $e->getMessage(),
                    'last_checked_at' => now(),
                ]
            );
        }
    }
}

