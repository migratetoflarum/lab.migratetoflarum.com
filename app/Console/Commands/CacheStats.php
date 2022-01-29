<?php

namespace App\Console\Commands;

use App\Stats;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheStats extends Command
{
    protected $signature = 'stats:cache {--clear}';

    public function handle()
    {
        if ($this->option('clear')) {
            Cache::forget('stats');

            $this->info('Cache cleared');
        } else {
            Cache::forever('stats', (new Stats())());

            $this->info('Stats cached');
        }
    }
}
