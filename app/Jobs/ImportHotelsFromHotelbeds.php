<?php

namespace App\Jobs;

use App\Models\Hotel;
use App\Models\ImportJob;
use App\Services\HotelbedsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ImportHotelsFromHotelbeds implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected ImportJob $job;

    public function __construct(ImportJob $job)
    {
        $this->job = $job;
    }

    public function handle(HotelbedsService $hb)
    {
        $this->job->update(['status' => 'running']);
        try {
            // This is a simplified example. Real Hotelbeds offers bulk endpoints (content API).
            $payload = $this->job->payload ?? [];
            // Suppose we use availability to fetch hotel info (adjust to actual content API)
            $res = $hb->availability($payload['availability_payload'] ?? [
                'stay' => ['checkIn' => now()->toDateString(), 'checkOut' => now()->addDay()->toDateString()],
                'occupancies' => [['rooms' => 1, 'adults' => 1]],
                'hotelIds' => $payload['hotelIds'] ?? []
            ]);

            if (!is_array($res) || empty($res)) {
                $this->job->update(['status' => 'failed', 'log' => 'Empty response']);
                return;
            }

            // Example mapping — adapt to real HB schema
            $hotels = Arr::get($res, 'hotels', Arr::get($res, 'hotels', []));
            if (empty($hotels) && isset($res['hotels'][0])) {
                $hotels = $res['hotels'];
            }

            foreach ($hotels as $h) {
                $hotel = Hotel::updateOrCreate(
                    ['vendor_id' => $h['code'] ?? ($h['hotelCode'] ?? null)],
                    [
                        'name' => $h['name'] ?? ($h['hotelName'] ?? 'Unknown'),
                        'country' => $h['destination']['country'] ?? null,
                        'city' => $h['destination']['city'] ?? null,
                        'vendor' => 'hotelbeds',
                        'meta' => $h,
                        'status' => 'active',
                    ]
                );
                // rooms import omitted for brevity
            }

            $this->job->update(['status' => 'done', 'log' => 'Imported '.count($hotels).' hotels']);
        } catch (\Throwable $e) {
            Log::error('ImportJob error: '.$e->getMessage());
            $this->job->update(['status' => 'failed', 'log' => $e->getMessage()]);
        }
    }
}
