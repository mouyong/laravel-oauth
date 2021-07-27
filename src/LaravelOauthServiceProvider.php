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
        ], 'config');

        $this->publishes([
            __DIR__.'/../routes/oauth.php' => \base_path('routes/oauth.php'),
        ], 'route');
    }
}
