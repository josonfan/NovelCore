<?php
return [
    'enable_types' => [
        'user',
        'comment',
        'order',
        'stats',
        'ticket',
        'ticket_attachment',
        'feedback',
        'feedback_attachment',
        'ticket_reply',
    ],
    'push_path' => '/Sync/receive',
    'timeout' => 5,
    'sync_data' => true,
];
