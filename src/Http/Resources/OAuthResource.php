<?php

namespace ZhenMu\LaravelOauth\Http\Resources;

use App\Models\User;
use ZhenMu\LaravelInitTemplate\Http\Resources\BaseResource;
use ZhenMu\LaravelOauth\Models\OAuth;

class OAuthResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var OAuth $oauth */
        /** @var User $user */
        list($oauth, $user, $access_token) = array_values($this->resource);

        $mobile = optional($user)->mobile;

        return [
            'oauth' => [
                'id' => optional($oauth)->id,
                'avatar' => optional($oauth)->avatar,
                'nickname' => optional($oauth)->nickname,
                'is_need_bind_user' => !optional($oauth)->user_id,
            ],
            'user' => [
                'id' => optional($user)->id,
                'avatar' => optional($user)->avatar,
                'name' => optional($user)->name,
                'mobile' => $mobile,
                'is_need_bind_id_card' => !!optional($user)->id_card,
            ],
            'access_token' => $access_token,
        ];
    }
}
