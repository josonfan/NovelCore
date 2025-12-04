<?php

namespace app\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return json_success([
            'version' => \think\facade\App::version(),
        ]);
    }

    public function hello($name = 'ThinkPHP8')
    {
        return json_success([
            'greeting' => 'hello,' . $name,
        ]);
    }
}
