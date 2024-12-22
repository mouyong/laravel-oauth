<?php

return [
    'migrations' => true,

    'oauth_model' => \ZhenMu\LaravelOauth\Models\Oauth::class,

    'route' => [
        'enable' => true,

        'middleware' => [],

        'prefix' => 'api',
    ],
];