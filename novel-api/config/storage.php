<?php
// 存储配置（B2 占位），未来可扩展至多驱动
return [
    'default' => env('STORAGE_DRIVER', 'b2'),
    'cdn_domain' => env('IMAGE_DOMAIN', ''), // CDN 域名，getPublicUrl 时优先使用
    'drivers' => [
        'b2' => [
            'key_id'          => env('B2_KEY_ID', ''),
            'application_key' => env('B2_APPLICATION_KEY', ''),
            'bucket_name'     => env('B2_BUCKET', ''),
            'endpoint'        => env('B2_ENDPOINT', ''),
        ],
    ],
];
