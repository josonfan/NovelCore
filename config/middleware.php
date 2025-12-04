<?php

return [
    'alias'    => [
        'cors' => \app\middleware\CorsMiddleware::class,
        'auth' => \app\middleware\AuthMiddleware::class,
        'api_log' => \app\middleware\ApiAccessLogMiddleware::class,
        'admin_auth' => \app\middleware\AdminAuthMiddleware::class,
    ],
    'priority' => [
        \app\middleware\ApiAccessLogMiddleware::class,
        \app\middleware\CorsMiddleware::class,
        \app\middleware\AuthMiddleware::class,
        \app\middleware\AdminAuthMiddleware::class,
    ],
];
