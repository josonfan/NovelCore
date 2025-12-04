<?php

return [
    'default' => [
        'host'       => env('REDIS_HOST', '127.0.0.1'),
        'port'       => env('REDIS_PORT', 6379),
        'password'   => env('REDIS_PASSWORD', ''),
        'select'     => env('REDIS_DB', 0),
        'timeout'    => 0,
        'persistent' => false,
    ],
];
