<?php

namespace App\Jobs;

use App\Events\ScanUpdated;
use App\Exceptions\TaskManualFailException;
use App\Task;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class ScanUpdateDatabase extends TaskJob
{
    protected function handleTask()
    {
        $scan = $this->task->scan;

        $scan->report = [];
        $scan->scanned_at = Carbon::now();

        try {
            $rating = $this->siblingTask(ScanRate::class);
            $scan->rating = $rating->getData('rating');
        } catch (TaskManualFailException $exception) {
            $this->log(self::LOG_PUBLIC, 'No rating available, skipping');
        }

        $scan->save();

        try {
            $alternate = $this->siblingTask(ScanAlternateUrlsAndHeaders::class);
            $canonicalUrl = $alternate->getData('firstWorkingUrl');

            if ($canonicalUrl && $scan->website->canonical_url !== $canonicalUrl) {
                $scan->website->canonical_url = $canonicalUrl;
            }
        } catch (TaskManualFailException $exception) {
            $this->log(self::LOG_PUBLIC, 'No canonical url available, skipping');
        }

        try {
            $homepage = $this->siblingTask(ScanHomePage::class);
            $title = $homepage->getData('bootTitle');

            if ($title && $scan->website->name !== $title) {
                $scan->website->name = $title;
            }
        } catch (TaskManualFailException $exception) {
            $this->log(self::LOG_PUBLIC, 'No title available, skipping');
        }

        if ($scan->rating && $scan->website->last_rating !== $scan->rating) {
            $scan->website->last_rating = $scan->rating;
        }

        if (!$scan->hidden) {
            $scan->website->last_public_scanned_at = $scan->scanned_at;
        }

        $isFlarum = Task::query()
            ->where('scan_id', $this->task->scan_id)
            ->where('job', ScanHomePage::class)
            ->whereNotNull('completed_at')
            ->exists();

        $scan->website->updateIsFlarumStatus($isFlarum);

        // We don't update the ping date here, because otherwise updating the ping but not running the showcase would
        // greatly delay the next scheduled ping+showcase

        if ($scan->website->isDirty()) {
            $scan->website->save();
        }

        event(new ScanUpdated($scan));

        // If Flarum was detected and the website is public and the showcase meta is older than a day, update
        if ($scan->website->is_flarum && !$scan->website->ignore && !$scan->hidden) {
            $lastShowcaseUpdate = Arr::get($scan->website->showcase_meta, 'date');

            if (!$lastShowcaseUpdate || Carbon::parse($lastShowcaseUpdate)->lt(now()->subDay())) {
                ShowcaseUpdate::dispatch($scan->website);
                ShowcaseScreenshot::dispatch($scan->website);

                // If the showcase update was triggered, we can update the ping date so the
                // next scheduled ping+showcase is postponed
                $scan->website->pinged_at = now();
                $scan->website->save();
            }
        }
    }
}
