<?php

return [    
    'middleware' => [
        \app\middleware\SiteInit::class,
    ],
    'alias'    => [
        'site_init' => \app\middleware\SiteInit::class,
        'cors' => \app\middleware\Cors::class,
        'auth' => \app\middleware\Auth::class,
        'api_log' => \app\middleware\ApiAccessLog::class,
        'admin_auth' => \app\middleware\AdminAuth::class,
        'admin_push' => \app\middleware\AdminPushAuth::class,
    ],
    'priority' => [
        \app\middleware\SiteInit::class,
        \app\middleware\ApiAccessLog::class,
        \app\middleware\Cors::class,
        \app\middleware\Auth::class,
        \app\middleware\AdminAuth::class,
    ],
];
