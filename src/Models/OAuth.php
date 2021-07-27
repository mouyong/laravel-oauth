<?php

namespace ZhenMu\LaravelOauth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use ZhenMu\LaravelInitTemplate\Models\BaseModel;

class OAuth extends BaseModel
{
    use HasFactory;

    const PLATFORM_OFFICIAL = 1;
    const PLATFORM_MINI_PROGRAM = 2;
    const PLATFORM_MAP = [
        OAuth::PLATFORM_OFFICIAL => '微信公众号',
        OAuth::PLATFORM_MINI_PROGRAM => '微信小程序',
    ];

    const GENDER_UNKNOWN = 'UNKNOWN';
    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';
    const GENDER_MAP = [
        OAuth::GENDER_UNKNOWN => 0,
        OAuth::GENDER_MALE => 1,
        OAuth::GENDER_FEMALE => 2,
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
        return OAuth::PLATFORM_MAP[$this->platform] ?? '未知平台';
    }

    public function user()
    {
        return $this->belongsTo(config('laravel-oauth.user_model', \App\Models\User::class));
    }

    public static function translateGender(int $gender)
    {
        return array_flip(OAuth::GENDER_MAP)[$gender] ?? OAuth::GENDER_UNKNOWN;
    }
}
