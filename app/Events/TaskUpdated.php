<?php

namespace App\Events;

use App\Resources\TaskResource;

class TaskUpdated extends AbtractTaskEvent
{
    public function broadcastWith(): array
    {
        return array_except((new TaskResource($this->task))->jsonSerialize(), [
            'attributes.public_log',
        ]);
    }
}
