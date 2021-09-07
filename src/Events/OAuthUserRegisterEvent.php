<?php

namespace ZhenMu\LaravelOauth\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ZhenMu\LaravelOauth\Models\Oauth;

class OAuthUserRegisterEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array
     */
    public $data;

    /**
     * @var Oauth
     */
    public $oauth;

    /**
     * Create a new event instance.
     *
     * @param array $data
     * @param Oauth|null $oauth
     */
    public function __construct(array $data, ?Oauth $oauth = null)
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
