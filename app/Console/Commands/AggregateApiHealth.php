<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HealthAggregationService;
use Carbon\Carbon;

class AggregateApiHealth extends Command
{
    protected $signature = 'health:aggregate {--date=}';
    protected $description = 'Aggregate API health metrics';

    public function handle(HealthAggregationService $service)
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now()->subDay();

        $service->aggregateAvailabilityDaily($date);

        $this->info('API health aggregated for ' . $date->toDateString());
    }
}
