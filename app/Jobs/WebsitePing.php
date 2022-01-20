<?php

namespace App\Jobs;

use App\ScannerClient;
use App\Website;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\DomCrawler\Crawler;

class WebsitePing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Website $website;

    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    public function handle()
    {
        /**
         * @var $client ScannerClient
         */
        $client = app(ScannerClient::class);

        $isFlarum = false;

        try {
            $response = $client->get($this->website->canonical_url);

            if ($response->getStatusCode() === 200) {
                $page = new Crawler($response->getBody()->getContents());

                $meta = $page->filter('meta[name=migratetoflarum-lab-opt-out]')->first();

                // If the website wasn't ignored but we notice it should be, add ignore flag
                // Let the opt out check take care of removing websites from the opt out
                if (!$this->website->ignore && $meta->count() > 0) {
                    $this->website->ignore = true;
                }

                $page->filter('head link[rel="stylesheet"]')->each(function (Crawler $link) use (&$isFlarum) {
                    $href = $link->attr('href');

                    if (str_contains($href, '/assets/forum-')) {
                        $isFlarum = true;
                    }
                });
            }
        } catch (RequestException $exception) {
            // Ignore connect exceptions, they will be considered as non-flarum
            // We also need to catch RequestException for certificate issues and such
        }

        $this->website->updateIsFlarumStatus($isFlarum);
        $this->website->pinged_at = now();
        $this->website->save();
    }
}
