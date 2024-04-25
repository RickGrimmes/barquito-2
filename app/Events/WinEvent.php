<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WinEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $boads;
    public function __construct($boads)
    {
        $this->boads = $boads;
    }
    public function broadcastOn(): array
    {
        return [
            new Channel('boad-game'),
        ];
    }
    public function broadcastAs()
    {
        return 'boad-game-event';
    }
}
