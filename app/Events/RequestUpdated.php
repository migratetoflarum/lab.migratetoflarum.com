<?php

namespace App\Events;

use App\Request;
use App\Resources\RequestResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class RequestUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function broadcastOn()
    {
        return new Channel('scans.' . $this->request->scan->uid);
    }

    public function broadcastWith(): array
    {
        return Arr::except((new RequestResource($this->request))->jsonSerialize(), [
            'attributes.response_body', // Not including body because it might be too big for Pusher
        ]);
    }
}
