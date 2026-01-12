<?php

namespace App\Jobs;

use App\Models\Hotel;
use App\Services\HotelExclusionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateHotelExclusionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900; // 15 minutes
    public int $tries = 3;

    public function handle(HotelExclusionService $service): void
    {
        Hotel::select('id')->chunkById(500, function ($hotels) use ($service) {
            foreach ($hotels as $hotel) {
                $hotel = Hotel::with(['media', 'exclusion'])->find($hotel->id);

                $result = $service->evaluate($hotel);

                if ($result['source'] === 'automatic') {
                    $hotel->update([
                        'auto_hidden' => !$result['visible'],
                        'auto_exclusion_reasons' => $result['reasons'],
                    ]);
                }
            }
        });
    }
}
