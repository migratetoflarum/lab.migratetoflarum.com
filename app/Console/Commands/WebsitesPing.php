<?php

namespace App\Console\Commands;

use App\Jobs\ShowcaseUpdate;
use App\Jobs\WebsitePing;
use App\Website;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class WebsitesPing extends Command
{
    protected $signature = 'websites:ping {website?}';
    protected $description = 'Ping all or a specific website';

    public function handle()
    {
        if ($this->argument('website')) {
            $websites = [Website::where('uid', $this->argument('website'))->firstOrFail()];
        } else {
            $websites = Website::where('ignore', '=', 0)
                ->where('is_flarum', '=', 1)
                ->where(function (Builder $query) {
                    $query->whereNull('pinged_at')
                        ->orWhere('pinged_at', '<', now()->subDays(config('scanner.ping.interval')));
                })
                ->get();
        }

        foreach ($websites as $website) {
            /**
             * @var $website Website
             */
            $this->info('Dispatching ping for ' . $website->normalized_url);
            WebsitePing::withChain([
                new ShowcaseUpdate($website)
            ])->dispatch($website);
        }
    }
}
