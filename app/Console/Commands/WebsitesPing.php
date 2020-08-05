<?php

namespace App\Console\Commands;

use App\Jobs\ShowcaseScreenshot;
use App\Jobs\ShowcaseUpdate;
use App\Jobs\WebsitePing;
use App\Website;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class WebsitesPing extends Command
{
    protected $signature = 'websites:ping {website?} {--since-days=default}';
    protected $description = 'Ping all or a specific website';

    public function handle()
    {
        if ($this->argument('website')) {
            $websites = [Website::query()->where('uid', $this->argument('website'))->firstOrFail()];
        } else {
            $sinceDays = $this->option('since-days') === 'default' ? config('scanner.ping.interval') : $this->option('since-days');

            $websites = Website::query()
                ->where('ignore', '=', 0)
                ->where('is_flarum', '=', 1)
                ->where(function (Builder $query) use ($sinceDays) {
                    $query->whereNull('pinged_at')
                        ->orWhere('pinged_at', '<', now()->subDays($sinceDays));
                })
                ->get();
        }

        foreach ($websites as $website) {
            /**
             * @var $website Website
             */
            $this->info('Dispatching ping for ' . $website->normalized_url);
            WebsitePing::withChain([
                new ShowcaseUpdate($website),
                new ShowcaseScreenshot($website),
            ])->dispatch($website);
        }
    }
}
