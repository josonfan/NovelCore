<?php
// 向总后台上报用户/订单的接口配置
return [
    // 总后台基础 URL，例如 https://admin.example.com
    'base_url'        => env('REPORT_BASE_URL', ''),
    // 用户上报相对路径
    'users_endpoint'  => env('REPORT_USERS_ENDPOINT', '/api/report/users'),
    // 订单上报相对路径
    'orders_endpoint' => env('REPORT_ORDERS_ENDPOINT', '/api/report/orders'),
    // 后台颁发的站点令牌
    'token'           => env('REPORT_SITE_TOKEN', ''),
    // 默认回溯时间（秒），当无 last_sync 记录时使用
    'default_lookback' => (int) env('REPORT_DEFAULT_LOOKBACK', 3600),
];
