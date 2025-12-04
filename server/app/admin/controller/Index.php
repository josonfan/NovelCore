<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\BaseController;

class Index extends BaseController
{
    public function index(): \think\response\Json
    {
        return $this->json(['app' => 'admin']);
    }
}

