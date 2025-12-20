<?php
// 全局中间件按给定顺序执行
return [
    \app\middleware\Cors::class,
    \app\middleware\SiteInit::class,
    \app\middleware\ResponseTime::class,
    \think\middleware\LoadLangPack::class,
    \app\middleware\BotBlock::class,
];
