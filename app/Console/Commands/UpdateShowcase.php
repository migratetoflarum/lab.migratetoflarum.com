<?php

namespace App\Console\Commands;

use App\Jobs\ShowcaseScreenshot;
use App\Jobs\ShowcaseUpdate;
use App\Website;
use Illuminate\Console\Command;

class UpdateShowcase extends Command
{
    protected $signature = 'showcase:update {website}';
    protected $description = 'Launch the showcase update job for a website';

    public function handle()
    {
        /**
         * @var $website Website
         */
        $website = Website::query()
            ->where('uid', $this->argument('website'))
            ->firstOrFail();

        dispatch(new ShowcaseUpdate($website));
        dispatch(new ShowcaseScreenshot($website));
    }
}
