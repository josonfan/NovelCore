<?php
// 全局中间件按给定顺序执行
return [
    \app\middleware\ApiAccessLog::class,
    \app\middleware\Cors::class,
    \app\middleware\SiteInit::class,
];
