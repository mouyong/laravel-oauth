<?php

namespace ZhenMu\LaravelOauth\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ZhenMu\LaravelOauth\Models\OAuth;

class OAuthUserRegisterEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array
     */
    public $data;

    /**
     * @var OAuth
     */
    public $oauth;

    /**
     * Create a new event instance.
     *
     * @param array $data
     * @param OAuth|null $oauth
     */
    public function __construct(array $data, ?OAuth $oauth = null)
    {
        $this->data = $data;
        $this->oauth = $oauth;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
