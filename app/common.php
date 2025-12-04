<?php
declare(strict_types=1);

use think\Response;

if (!function_exists('json_success')) {
    function json_success($data = [], string $message = 'OK', int $code = 0): Response
    {
        return json([
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ]);
    }
}

if (!function_exists('json_error')) {
    function json_error(string $message, int $code = 1, $data = []): Response
    {
        return json([
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ]);
    }
}
