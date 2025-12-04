<?php
// JWT 配置：从环境变量读取密钥与过期时间
return [
    // 签名密钥
    'secret'      => env('JWT_SECRET', ''),
    // 访问 Token 有效期（秒）
    'ttl'         => (int) env('JWT_TTL', 3600),
    // 可选刷新 Token 有效期（秒）
    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 1209600),
];
