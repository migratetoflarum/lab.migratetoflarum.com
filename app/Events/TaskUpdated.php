<?php

namespace App\Events;

use App\Resources\TaskResource;
use Illuminate\Support\Arr;

class TaskUpdated extends AbstractTaskEvent
{
    public function broadcastWith(): array
    {
        return Arr::except((new TaskResource($this->task))->jsonSerialize(), [
            // This broadcast is only to update the state of a task
            // We'll skip all data that could make the payload too large for Pusher
            'attributes.data',
            'attributes.public_log',
            'attributes.private_log',
        ]);
    }
}
