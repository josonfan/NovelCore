<?php

return [
    'app_namespace'       => '',
    'with_route'          => true,
    'default_app'         => 'index',
    'default_timezone'    => 'Asia/Shanghai',
    'default_return_type' => 'json',

    'app_map'       => [],
    'domain_bind'   => [],
    'deny_app_list' => [],

    'exception_tmpl' => app()->getThinkPath() . 'tpl/think_exception.tpl',

    'error_message'  => 'Server error, please try again later.',
    'show_error_msg' => false,
];
