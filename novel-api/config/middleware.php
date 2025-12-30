<?php

return [ 
    'alias'    => [
        'lang' => \think\middleware\LoadLangPack::class,
        'site_init' => \app\middleware\SiteInit::class,
        'bot_block' => \app\middleware\BotBlock::class,
        'cors' => \app\middleware\Cors::class,
        'auth' => \app\middleware\Auth::class,
        'admin_auth' => \app\middleware\AdminAuth::class,
        'admin_push' => \app\middleware\AdminPushAuth::class,
        'response_time' => \app\middleware\ResponseTime::class,
        'no_auth' => \app\middleware\NoAuth::class,
        'validate_email_code' => \app\middleware\ValidateEmailCode::class,
    ],
    'priority' => [
        \think\middleware\LoadLangPack::class,
        \app\middleware\SiteInit::class,
        \app\middleware\NoAuth::class,
        \app\middleware\BotBlock::class,
        \app\middleware\ResponseTime::class,
        \app\middleware\Cors::class,
        \app\middleware\Auth::class,
        \app\middleware\AdminAuth::class,
        \app\middleware\ValidateEmailCode::class,
    ],
];
