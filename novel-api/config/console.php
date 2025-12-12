<?php
// 控制台命令配置
return [
    'commands' => [
        'data:sync' => \app\command\DataSync::class,
        'daily:stats' => \app\command\DailyStats::class,
        'sync:user-order' => \app\command\SyncUserOrder::class,
        'user:stats-sync' => \app\command\UserStatsSync::class,
        'user:week-rebuild' => \app\command\UserWeekStatsRebuild::class,
    ],
];
