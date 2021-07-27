<?php

namespace ZhenMu\LaravelOauth\Entities;

use ZhenMu\LaravelOauth\Contracts\OauthContract;
use ZhenMu\LaravelOauth\Models\OAuth;

class WechatOauth extends AbstractOauth implements OauthContract
{
    public function getPlatform(): int
    {
        return OAuth::PLATFORM_OFFICIAL;
    }

    public function getGender(): string
    {
        return OAuth::translateGender($this->data['sex']);
    }

    public function getAvatar(): string
    {
        return $this->data['headimgurl'];
    }
}