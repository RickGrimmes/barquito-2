<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameCanelEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cancel;
    public function __construct($cancel)
    {
        $this->cancel = $cancel;
    }
    public function broadcastOn(): array
    {
        return [
            new Channel('game-cancel-game'),
        ];
    }
    public function broadcastAs()
    {
        return 'game-cancel-event';
    }
}
