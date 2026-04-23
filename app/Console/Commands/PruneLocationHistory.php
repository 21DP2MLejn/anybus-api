<?php

namespace App\Console\Commands;

use App\Models\WorkerLocationHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class PruneLocationHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune-location-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old worker location history records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $enabled = Config::get('services.geolocation.prune_enabled', true);
        $days = Config::get('services.geolocation.prune_days', 7);

        if (! $enabled) {
            $this->info('Location history pruning is disabled.');

            return;
        }

        $this->info("Pruning location history older than {$days} days...");

        $count = WorkerLocationHistory::where('recorded_at', '<', now()->subDays($days))->delete();

        $this->info("Successfully deleted {$count} records.");
        Log::info("Pruned {$count} worker location history records older than {$days} days.");
    }
}
