<?php

namespace ZhenMu\LaravelOauth\Http\Resources;

use ZhenMu\LaravelInitTemplate\Http\Resources\BaseResource;
use ZhenMu\LaravelOauth\Models\Oauth;

class OauthProfileResource extends BaseResource
{
    public function toArray($request)
    {
        /** @var Oauth $oauthModel */
        $oauthModel = $this->resource;

        return [
            'oauth_id' => $oauthModel->id,
            'platform' => $oauthModel->platform,
            'platform_desc' => $oauthModel->platform_desc,
            'open_id' => $oauthModel->open_id,
            'nickname' => $oauthModel->nickname,
            'avatar' => $oauthModel->avatar,
            'gender' => $oauthModel->gender,
            'user_id' => $oauthModel->user_id,
        ];
    }
}