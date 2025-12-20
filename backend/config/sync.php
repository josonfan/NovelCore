<?php
return [
    'enable_types' => [
        'domain_list',
        'comment_config',
        'comment_review_config',
        'customer_service_config',
        'search_config',
        'email_config',
        'storage_config',
        'telegram_audit_config',
        'categories',
        'tags',
        'novels',
        'novel_tags',
        'chapters',
        'chapter_contents', 
        'site_comments',
        'site_users',
    ],
    'push_path' => '/Sync/receive',
    'timeout' => 5,
    'sync_data' => true,
];
