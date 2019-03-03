<?php

namespace App\Console\Commands;

use App\Jobs\ShowcaseUpdate;
use App\Website;
use Illuminate\Console\Command;

class UpdateShowcase extends Command
{
    protected $signature = 'showcase:update {website}';
    protected $description = 'Launch the showcase update job for a website';

    public function handle()
    {
        $website = Website::where('uid', $this->argument('website'))->firstOrFail();

        dispatch(new ShowcaseUpdate($website));
    }
}
