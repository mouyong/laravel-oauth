<?php

use ZhenMu\LaravelOauth\Http\Controllers as Controller;
use Illuminate\Support\Facades\Route;

Route::middleware(config('laravel-oauth.route.middleware', []))->prefix(config('laravel-oauth.route.prefix', 'api'))->group(function () {
    Route::any('oauth/{platform}/update-info', [Controller\OAuthController::class, 'updateInfo'])->name('oauth.update-info');
    Route::post('oauth/{platform}/bind-user-info', [Controller\OAuthController::class, 'bindUserInfo']);
    Route::get('oauth/{platform}', [Controller\OAuthController::class, 'redirect']);
});