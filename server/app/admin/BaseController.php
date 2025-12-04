<?php
declare(strict_types=1);

namespace app\admin;

class BaseController
{
    protected function json(array $data = [], string $message = '', int $code = 200): \think\response\Json
    {
        return json(['data' => $data, 'message' => $message, 'code' => $code]);
    }
}

