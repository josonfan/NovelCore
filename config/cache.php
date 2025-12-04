<?php

return [
    'default' => env('CACHE_STORE', 'file'),

    'stores'  => [
        'file' => [
            'type'       => 'File',
            'path'       => '',
            'prefix'     => env('CACHE_PREFIX', ''),
            'expire'     => 0,
            'tag_prefix' => 'tag:',
            'serialize'  => [],
        ],
        'redis' => [
            'type'     => 'redis',
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'port'     => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', ''),
            'select'   => env('REDIS_DB', 0),
            'timeout'  => 0,
            'expire'   => 0,
            'prefix'   => env('CACHE_PREFIX', ''),
        ],
    ],
];
