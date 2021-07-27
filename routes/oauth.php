<?php

use ZhenMu\LaravelOauth\Http\Controllers as Controller;
use Illuminate\Support\Facades\Route;

Route::middleware(config('laravel-oauth.route.middleware', []))->prefix(config('laravel-oauth.route.prefix', 'api'))->group(function () {
    Route::any('oauth/{platform}/update-info', [Controller\OauthController::class, 'updateInfo']);
    Route::post('oauth/{platform}/bind-user-info', [Controller\OauthController::class, 'bindUserInfo']);
    Route::get('oauth/{platform}', [Controller\OauthController::class, 'redirect']);
});