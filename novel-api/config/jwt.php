<?php
// JWT 配置：从环境变量读取密钥与过期时间
return [
    'secret'      => env('JWT.SECRET', ''),
    'ttl'         => (int) env('JWT.TTL', 3600),
    'refresh_ttl' => (int) env('JWT.REFRESH_TTL', 1209600),
    'issuer'      => env('JWT.ISSUER', ''),
    'audience'    => env('JWT.AUDIENCE', ''),
    'leeway'      => (int) env('JWT.LEEWAY', 0),
];
