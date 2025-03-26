<?php

namespace ZhenMu\LaravelOauth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use ZhenMu\LaravelInitTemplate\Models\BaseModel;

class Oauth extends BaseModel
{
    use HasFactory;

    const PLATFORM_OFFICIAL = 1;
    const PLATFORM_MINI_PROGRAM = 2;
    const PLATFORM_MAP = [
        Oauth::PLATFORM_OFFICIAL => '微信公众号',
        Oauth::PLATFORM_MINI_PROGRAM => '微信小程序',
    ];

    const GENDER_UNKNOWN = 'UNKNOWN';
    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';
    const GENDER_MAP = [
        Oauth::GENDER_UNKNOWN => 0,
        Oauth::GENDER_MALE => 1,
        Oauth::GENDER_FEMALE => 2,
    ];

    protected $fillable = [
        'user_id', 'app_id', 'platform', 'open_id', 'union_id', 'session_key', 'nickname', 'gender', 'avatar',
        'country', 'province', 'city', 'original',
    ];

    protected $casts = [
        'original' => 'json'
    ];

    public function getPlatformDescAttribute()
    {
        return Oauth::PLATFORM_MAP[$this->platform] ?? '未知平台';
    }

    public function user()
    {
        return $this->belongsTo(config('laravel-init-template.user_model', \App\Models\User::class));
    }

    public static function translateGender(int $gender)
    {
        return array_flip(Oauth::GENDER_MAP)[$gender] ?? Oauth::GENDER_UNKNOWN;
    }
}
