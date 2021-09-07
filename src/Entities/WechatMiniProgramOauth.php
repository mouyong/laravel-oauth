<?php

namespace ZhenMu\LaravelOauth\Entities;

use ZhenMu\LaravelOauth\Contracts\OauthContract;
use ZhenMu\LaravelOauth\Models\Oauth;

class WechatMiniProgramOauth extends AbstractOauth implements OauthContract
{
    public function getPlatform(): int
    {
        return Oauth::PLATFORM_MINI_PROGRAM;
    }

    public function getNickname(): ?string
    {
        return $this->data['nickName'] ?? null;
    }

    public function getAvatar(): string
    {
        return $this->data['avatarUrl'];
    }
}