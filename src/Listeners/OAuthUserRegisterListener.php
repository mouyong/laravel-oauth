<?php

namespace ZhenMu\LaravelOauth\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use ZhenMu\LaravelInitTemplate\Repositories\UserRepository;
use ZhenMu\LaravelOauth\Events\OAuthUserRegisterEvent;

class OAuthUserRegisterListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param OAuthUserRegisterEvent $event
     * @return void
     */
    public function handle(OAuthUserRegisterEvent $event)
    {
        $oauth = $event->oauth;

        $mobile = \request()->get('mobile');

        $user = app(UserRepository::class)->createByOAuth([
            'parent_id' => \request()->get('parent_id'),
            'name' => $oauth->nickname ?? $mobile,
            'realname' => \request()->get('realname'),
            'mobile' => $mobile,
            'avatar' => $oauth->avatar ?? null,
            'id_card' => \request()->get('id_card'),
            'ip' => \request()->header('x-forwarded-for', \request()->ip()),
        ]);

        if ($oauth) {
            $oauth->user_id = $user->id;
            $oauth->save();
        }
    }
}
