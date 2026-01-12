<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Jobs\SyncHotelsFromHotelbeds;

class HotelbedsSync extends Command
{
    protected $signature = 'hotelbeds:sync {--hotelIds=}';

    public function handle()
    {
        $hotelIds = $this->option('hotelIds') ? explode(',', $this->option('hotelIds')) : [];
        SyncHotelsFromHotelbeds::dispatch($hotelIds);
        $this->info('Sync dispatched');
    }
}
