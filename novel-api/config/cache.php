<?php

return [
    'default' => env('CACHE.driver', 'file'),

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
            'host'     => env('REDIS.HOST', '127.0.0.1'),
            'port'     => env('REDIS.PORT', 6379),
            'password' => env('REDIS.PASSWORD', ''),
            'select'   => env('REDIS.SELECT', 0),
            'timeout'  => env('REDIS.TIMEOUT', 0),
            'expire'   => env('REDIS.EXPIRE', 0),
            'prefix'   => env('REDIS.PREFIX', ''),
        ],
    ],
];
