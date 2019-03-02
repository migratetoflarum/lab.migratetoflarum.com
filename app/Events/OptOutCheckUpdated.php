<?php

namespace App\Events;

use App\OptOutCheck;
use App\Resources\OptOutCheckResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class OptOutCheckUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $check;

    public function __construct(OptOutCheck $check)
    {
        $this->check = $check;
    }

    public function broadcastOn()
    {
        return new Channel('opt-out-checks.' . $this->check->uid);
    }

    public function broadcastWith(): array
    {
        return (new OptOutCheckResource($this->check))->toArray(new Request());
    }
}
