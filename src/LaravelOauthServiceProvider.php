<?php

namespace ZhenMu\LaravelOauth;

use Illuminate\Support\ServiceProvider;

class LaravelOauthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-oauth.php',
            'laravel-oauth'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../config/wechat.php',
            'wechat'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        \config('laravel-oauth.migrations') && $this->loadMigrationsFrom(__DIR__.'/../migrations');
        \config('laravel-oauth.route.enable') && $this->loadRoutesFrom(__DIR__.'/../routes/oauth.php');

        $this->publishes([
            __DIR__.'/../migrations' => \database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../config/laravel-oauth.php' => \config_path('laravel-oauth.php'),
            __DIR__.'/../config/wechat.php' => \config_path('wechat.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../routes/oauth.php' => \base_path('routes/oauth.php'),
        ], 'route');
    }
}
