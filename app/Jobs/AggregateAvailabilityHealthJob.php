<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\HealthEventLog;
use App\Models\AvailabilityHealthDaily;

class AggregateAvailabilityHealthJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $logs = HealthEventLog::where('domain', 'availability')
            ->whereDate('created_at', today())
            ->get();

        $successRate = $logs->count()
            ? round($logs->where('status', 'success')->count() / $logs->count() * 100)
            : 0;

        AvailabilityHealthDaily::updateOrCreate(
            ['date' => today()],
            [
                'success_rate' => $successRate,
                'avg_response_ms' => round($logs->avg('response_time_ms')),
                'timeouts' => $logs->where('status', 'timeout')->count(),
                'no_results' => $logs->where('status', 'failure')->count(),
            ]
        );
    }

}
