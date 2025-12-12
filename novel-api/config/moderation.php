<?php
// 评论审核上报配置
return [
    // 审核服务地址，例如 https://admin.example.com/api/moderation
    'endpoint' => env('MODERATION_ENDPOINT', ''),
    // 访问审核服务使用的 token
    'token'    => env('MODERATION_TOKEN', ''),
];
