<?php

namespace ZhenMu\LaravelOauth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use ZhenMu\LaravelInitTemplate\Models\BaseModel;

class Profile extends BaseModel
{
    use HasFactory;

    protected $fillable = [];

    public function user()
    {
        return $this->belongsTo(config('laravel-oauth.user_model', \App\Models\User::class));
    }
}
