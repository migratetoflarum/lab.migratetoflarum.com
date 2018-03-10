<?php

namespace App\Events;

use App\Resources\ScanResource;
use App\Scan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScanUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $scan;

    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    public function broadcastOn()
    {
        return new Channel('scans.' . $this->scan->uid);
    }

    public function broadcastWith(): array
    {
        return (new ScanResource($this->scan))->jsonSerialize();
    }
}
