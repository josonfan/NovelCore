<?php
return [
    'enable_types' => [
        'user',
        'comment',
        'order',
        'stats',
        'ticket',
        'ticket_attachment',
    ],
    'push_path' => '/Sync/receive',
    'timeout' => 5,
    'sync_data' => true,
];
