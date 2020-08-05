<?php

namespace App\Jobs;

use App\Events\OptOutCheckUpdated;
use App\OptOutCheck;
use App\ScannerClient;
use App\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\DomCrawler\Crawler;

class IgnoreCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $check;

    public function __construct(OptOutCheck $check)
    {
        $this->check = $check;
    }

    public function handle()
    {
        /**
         * @var $client ScannerClient
         */
        $client = app(ScannerClient::class);

        $response = $client->get($this->check->canonical_url);

        $page = new Crawler($response->getBody()->getContents());

        // We only check for the presence of the meta, even though instructions say to add content=yes
        // Note: pre-august 2020 the instructions were saying to add value=yes
        $meta = $page->filter('meta[name=migratetoflarum-lab-opt-out]')->first();

        $this->check->ignore = $meta->count() > 0;
        $this->check->checked_at = now();
        $this->check->save();

        event(new OptOutCheckUpdated($this->check));

        /**
         * @var $website Website
         */
        $website = Website::where('normalized_url', $this->check->normalized_url)->first();

        if ($website) {
            if ($website->ignore !== $this->check->ignore) {
                $website->ignore = $this->check->ignore;
                $website->save();
            }
        } else {
            if ($this->check->ignore) {
                $website = new Website();
                $website->normalized_url = $this->check->normalized_url;
                $website->ignore = true;
                $website->save();
            }
        }
    }
}
