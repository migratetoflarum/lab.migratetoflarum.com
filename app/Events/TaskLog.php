<?php

namespace App\Events;

use App\Task;

class TaskLog extends AbtractTaskEvent
{
    protected $time;
    protected $message;

    public function __construct(Task $task, string $time, string $message)
    {
        parent::__construct($task);

        $this->time = $time;
        $this->message = $message;
    }

    public function broadcastWith(): array
    {
        return [
            'time' => $this->time,
            'message' => $this->message,
        ];
    }
}
