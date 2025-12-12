<?php

return [
    'default' => [
        'host'       => env('REDIS.HOST', '127.0.0.1'),
        'port'       => env('REDIS.PORT', 6379),
        'password'   => env('REDIS.PASSWORD', ''),
        'select'     => env('REDIS.SELECT', 0),
        'timeout'    => env('REDIS.TIMEOUT', 0),
        'persistent' => false,
    ],
];
