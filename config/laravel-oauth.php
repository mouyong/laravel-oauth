<?php

return [
    'migrations' => true,

    'user_foreign_key' => 'user_id',

    'oauth_model' => \ZhenMu\LaravelOauth\Models\OAuth::class,

    'user_model' => \App\Models\User::class,

    'route' => [
        'enable' => true,

        'middleware' => [],

        'prefix' => 'api',
    ],
];