<?php
// 邮件服务配置（SES 占位）
return [
    'driver'       => env('MAIL_DRIVER', 'ses'),
    'access_key'   => env('SES_ACCESS_KEY', ''),
    'secret_key'   => env('SES_SECRET_KEY', ''),
    'region'       => env('SES_REGION', ''),
    'from_address' => env('MAIL_FROM_ADDRESS', ''),
    'from_name'    => env('MAIL_FROM_NAME', ''),
];
