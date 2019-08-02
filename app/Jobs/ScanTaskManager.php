<?php

namespace App\Jobs;

use App\Scan;
use App\Task;
use Illuminate\Database\Eloquent\Collection;

class ScanTaskManager
{
    protected function finished(Collection $tasks, string $classname): bool
    {
        /**
         * @var Task $task
         */
        $task = $tasks->get($classname);

        if ($task) {
            return false;
        }

        return $task->completed_at || $task->failed_at;
    }

    protected function dispatch(Collection $tasks, string $classname)
    {
        /**
         * @var Task $task
         */
        $task = $tasks->get($classname);

        if (!$task->started_at) {
            dispatch(new $classname($task));
        }
    }

    public function next(Scan $scan)
    {
        $tasks = $scan->tasks->keyBy('job');

        if ($this->finished($tasks, ScanAlternateUrlsAndHeaders::class) && $this->finished($tasks, ScanExposedFiles::class) && $this->finished($tasks, ScanMapExtensions::class)) {
            $this->dispatch($tasks, ScanRate::class);
        }

        if ($this->finished($tasks, ScanHomePage::class) && $this->finished($tasks, ScanJavascript::class)) {
            $this->dispatch($tasks, ScanMapExtensions::class);
        }

        if ($this->finished($tasks, ScanResolveCanonical::class)) {
            $this->dispatch($tasks, ScanAlternateUrlsAndHeaders::class);
            $this->dispatch($tasks, ScanExposedFiles::class);
            $this->dispatch($tasks, ScanHomePage::class);
            $this->dispatch($tasks, ScanJavascript::class);
        }

    }

    protected function createTask(Scan $scan, string $classname): Task
    {
        $task = new Task();
        $task->scan()->associate($scan);
        $task->job = $classname;
        $task->save();
    }

    public function start(Scan $scan)
    {
        $task = $this->createTask($scan, ScanResolveCanonical::class);
        $this->createTask($scan, ScanAlternateUrlsAndHeaders::class);
        $this->createTask($scan, ScanExposedFiles::class);
        $this->createTask($scan, ScanHomePage::class);
        $this->createTask($scan, ScanJavascript::class);
        $this->createTask($scan, ScanMapExtensions::class);
        $this->createTask($scan, ScanRate::class);

        dispatch(new ScanResolveCanonical($task));
    }
}
