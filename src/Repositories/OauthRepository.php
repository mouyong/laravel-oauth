<?php

namespace ZhenMu\LaravelOauth\Repositories;

class OauthRepository
{
    public function updateOrCreate(\ZhenMu\LaravelOauth\Contracts\OauthContract $oauth)
    {
        /** @var \ZhenMu\LaravelOauth\Models\Oauth $model */
        $model = config('laravel-oauth.oauth_model');

        return $model::query()
            ->updateOrCreate([
                'platform' => $oauth->getPlatform(),
                'open_id' => $oauth->getOpenId(),
            ], [
                'app_id' => $oauth->getAppId(),
                'union_id' => $oauth->getUnionId(),
                'session_key' => $oauth->getSessionKey(),
                'nickname' => $oauth->getNickname(),
                'gender' => $oauth->getGender(),
                'avatar' => $oauth->getAvatar(),
                'country' => $oauth->getCountry(),
                'province' => $oauth->getProvince(),
                'city' => $oauth->getCity(),
                'original' => $oauth->getOriginalOauthData(),
            ]);
    }
}