<?php

namespace App\Jobs;

use App\Scan;
use App\Task;
use Illuminate\Database\Eloquent\Collection;

class ScanTaskManager
{
    /**
     * @var Scan $scan
     */
    protected $scan;
    /**
     * @var Collection|Task[]
     */
    protected $tasks;
    /**
     * @var string[]
     */
    public $dispatched = [];

    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    protected function finished(string $classname): bool
    {
        /**
         * @var Task $task
         */
        $task = $this->tasks->get($classname);

        if (!$task) {
            return false;
        }

        return $task->completed_at || $task->failed_at;
    }

    protected function dispatch(string $classname)
    {
        /**
         * @var Task $task
         */
        $task = $this->tasks->get($classname);

        if (!$task) {
            $task = $this->createTask($classname);

            dispatch(new $classname($task));

            $this->dispatched[] = $classname;
        }
    }

    public function next()
    {
        // Get a fresh copy of the tasks status
        $this->tasks = $this->scan->tasks()->get()->keyBy('job');

        if ($this->finished(ScanRate::class)) {
            $this->dispatch(ScanUpdateDatabase::class);
        }

        if (
            $this->finished(ScanAlternateUrlsAndHeaders::class) &&
            $this->finished(ScanExposedFiles::class) &&
            $this->finished(ScanMapExtensions::class) &&
            $this->finished(ScanGuessVersion::class)
        ) {
            $this->dispatch(ScanRate::class);
        }

        if ($this->finished(ScanHomePage::class) && $this->finished(ScanJavascript::class)) {
            $this->dispatch(ScanMapExtensions::class);
            $this->dispatch(ScanGuessVersion::class);
        }

        if ($this->finished(ScanHomePage::class)) {
            $this->dispatch(ScanAlternateUrlsAndHeaders::class);
            $this->dispatch(ScanExposedFiles::class);
            $this->dispatch(ScanJavascript::class);
        }

        if ($this->finished(ScanResolveCanonical::class)) {
            $this->dispatch(ScanHomePage::class);
        }
    }

    protected function createTask(string $classname): Task
    {
        $task = new Task();
        $task->website_id = $this->scan->website_id; // Not using associate() to save one query
        $task->scan()->associate($this->scan);
        $task->job = $classname;
        $task->data = new \stdClass();
        $task->public_log = '';
        $task->private_log = '';
        $task->save();

        return $task;
    }

    public function start()
    {
        $task = $this->createTask(ScanResolveCanonical::class);

        dispatch(new ScanResolveCanonical($task));
    }
}
