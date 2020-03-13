<?php

namespace App\Jobs;

use App\Report\RatingAgent;

class ScanRate extends TaskJob
{
    public function handleTask()
    {
        $agent = new RatingAgent(
            $this->siblingTask(ScanResolveCanonical::class),
            $this->siblingTask(ScanHomePage::class),
            $this->siblingTask(ScanAlternateUrlsAndHeaders::class),
            $this->siblingTask(ScanExposedFiles::class)
        );

        $agent->rate();

        $this->data['rating'] = $agent->rating;
        $this->data['criteria'] = $agent->importantRules;
    }
}
