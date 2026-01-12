<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HealthAggregationService;
use Throwable;

class AggregateHealthDaily extends Command
{
    protected $signature = 'health:aggregate-daily';

    protected $description = 'Aggregate daily API health metrics (availability, booking, pricing)';

    public function handle(HealthAggregationService $service): int
    {
        $date = now()->subDay();

        try {
            $date = $this->argument('date')
                ? \Carbon\Carbon::parse($this->argument('date'))
                : now()->subDay();

            $service->aggregateAvailabilityDaily($date);
            $service->aggregateBookingDaily($date);
            $service->aggregatePricingDaily($date);

            $this->info("Health aggregation completed for {$date->toDateString()}");
            return self::SUCCESS;

        } catch (Throwable $e) {
            $this->error('Health aggregation failed');
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
