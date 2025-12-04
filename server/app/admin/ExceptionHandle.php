<?php
declare(strict_types=1);

namespace app\admin;

use think\exception\Handle;
use think\Response;

class ExceptionHandle extends Handle
{
    public function render($request, \Throwable $e): Response
    {
        return json(['data' => [], 'message' => $e->getMessage(), 'code' => 500]);
    }
}

