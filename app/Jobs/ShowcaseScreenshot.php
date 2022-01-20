<?php

namespace App\Jobs;

use App\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\Browsershot\Browsershot;

class ShowcaseScreenshot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Website $website;

    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    public function handle()
    {
        $this->website->clearMediaCollection(Website::COLLECTION_SCREENSHOT);

        if ($this->website->ignore || !$this->website->is_flarum || !$this->website->canonical_url) {
            return;
        }

        $rawImage = Browsershot::url($this->website->canonical_url)
            ->userAgent(config('scanner.client.user_agent'))
            ->windowSize(1400, 700)
            ->dismissDialogs()
            ->screenshot();

        $this->website->addMediaFromBase64(base64_encode($rawImage))
            ->setFileName($this->website->uid . '.png')
            ->toMediaCollection(Website::COLLECTION_SCREENSHOT);
    }
}
