<?php

namespace App\Events;

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
        // The report is sometimes too big for the sockets, so we just send the other parts of the report
        // We also skip relationships to reduce the size
        return [
            'type' => 'scans',
            'id' => $this->scan->uid,
            'attributes' => [
                'hidden' => $this->scan->hidden,
                'report' => null,
                'scanned_at' => optional($this->scan->scanned_at)->toW3cString(),
            ],
        ];
    }
}
