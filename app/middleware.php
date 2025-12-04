<?php
// 全局中间件按给定顺序执行
return [
    \app\middleware\ApiAccessLogMiddleware::class,
    \app\middleware\CorsMiddleware::class,
    // AdminAuth 仅用于 /api/admin 路由组，通过路由中间件启用
];
