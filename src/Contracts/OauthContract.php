<?php

namespace ZhenMu\LaravelOauth\Contracts;

interface OauthContract
{
    public function getPlatform(): int;

    public function getAppId(): string ;

    public function getOpenId(): string ;

    public function getUnionId(): ?string ;

    public function getSessionKey(): ?string;

    public function getNickname(): ?string ;

    public function getGender(): string ;

    public function getAvatar(): string ;

    public function getCountry(): string ;

    public function getProvince(): string ;

    public function getCity(): string ;

    public function getOriginalOauthData(): array ;
}