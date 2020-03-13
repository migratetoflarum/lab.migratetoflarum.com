<?php

namespace App\Events;

use App\Resources\TaskResource;
use Illuminate\Support\Arr;

class TaskUpdated extends AbstractTaskEvent
{
    public function broadcastWith(): array
    {
        return Arr::except((new TaskResource($this->task))->jsonSerialize(), [
            'attributes.public_log', // Could be too big for Pusher
            'attributes.private_log',
        ]);
    }
}
