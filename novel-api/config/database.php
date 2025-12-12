<?php

return [
    'default'         => 'mysql',
    'time_query_rule' => [],
    'auto_timestamp'  => true,
    'datetime_format' => 'Y-m-d H:i:s',
    'datetime_field'  => '',

    'connections' => [
        'mysql' => [
            'type'            => env('DATABASE.TYPE', 'mysql'),
            'hostname'        => env('DATABASE.HOSTNAME', '127.0.0.1'),
            'database'        => env('DATABASE.DATABASE', ''),
            'username'        => env('DATABASE.USERNAME', 'root'),
            'password'        => env('DATABASE.PASSWORD', ''),
            'hostport'        => env('DATABASE.HOSTPORT', '3306'),
            'params'          => [],
            'charset'         => env('DATABASE.CHARSET', 'utf8mb4'),
            'prefix'          => env('DATABASE.PREFIX', ''),
            'deploy'          => 0,
            'rw_separate'     => false,
            'master_num'      => 1,
            'slave_no'        => '',
            'fields_strict'   => true,
            'break_reconnect' => false,
            'trigger_sql'     => env('DATABASE.DEBUG', true),
            'fields_cache'    => false,
        ],
    ],
];
