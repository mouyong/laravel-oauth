<?php

namespace ZhenMu\LaravelOauth\Entities;

use ZhenMu\LaravelOauth\Contracts\OauthContract;
use ZhenMu\LaravelOauth\Models\Oauth;

abstract class AbstractOauth implements OauthContract
{
    protected $data;

    protected $app_id;

    public function __construct(string $app_id, array $data)
    {
        $this->app_id = $app_id;
        $this->data = $data;
    }

    public function getAppId(): string
    {
        return $this->app_id;
    }

    public function getOpenId(): string
    {
        return $this->data['openid'];
    }

    public function getUnionId(): ?string
    {
        return $this->data['unionid'] ?? null;
    }

    public function getSessionKey(): ?string
    {
        return $this->data['session_key'] ?? null;
    }

    public function getNickname(): ?string
    {
        return $this->data['nickname'] ?? null;
    }

    public function getGender(): string
    {
        return Oauth::translateGender($this->data['gender']);
    }

    public function getAvatar(): string
    {
        return $this->data['avatar'];
    }

    public function getCountry(): string
    {
        return $this->data['country'];
    }

    public function getProvince(): string
    {
        return $this->data['province'];
    }

    public function getCity(): string
    {
        return $this->data['city'];
    }

    public function getOriginalOauthData(): array
    {
        return $this->data;
    }
}