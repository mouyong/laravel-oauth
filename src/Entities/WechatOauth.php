<?php

namespace ZhenMu\LaravelOauth\Entities;

use ZhenMu\LaravelOauth\Contracts\OauthContract;
use ZhenMu\LaravelOauth\Models\Oauth;

class WechatOauth extends AbstractOauth implements OauthContract
{
    public function getPlatform(): int
    {
        return Oauth::PLATFORM_OFFICIAL;
    }

    public function getGender(): string
    {
        return Oauth::translateGender($this->data['sex']);
    }

    public function getAvatar(): string
    {
        return $this->data['headimgurl'];
    }
}