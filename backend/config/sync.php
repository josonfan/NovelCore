<?php
return [
    'enable_types' => [
        'categories',
        'tags',
        'novels',
        'novel_tags',
        'chapters',
        'domain_list',
        'comment_config',
        'comment_review_config',
        'customer_service_config',
        'search_config',
        'email_config',
        'storage_config',
        'telegram_audit_config'
    ],
    'push_path' => '/api/Sync/receive',
    'timeout' => 5,
    'sync_data' => true,
];
