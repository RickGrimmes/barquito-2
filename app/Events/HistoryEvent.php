<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HistoryEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $hostorial;
    public function __construct($hostorial)
    {
        $this->hostorial = $hostorial;
    }
    public function broadcastOn(): array
    {
        return [
            new Channel('history-game'),
        ];
    }
    public function broadcastAs()
    {
        return 'history-game-event';
    }
}
