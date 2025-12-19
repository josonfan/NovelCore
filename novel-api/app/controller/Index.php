<?php

namespace app\controller;


class Index extends Common
{
    public function index()
    {
        return $this->ajaxReturn(200, '成功', [
            'version' => \think\facade\App::version(),
        ]);
    }

    public function hello($name = 'ThinkPHP8')
    {
        return $this->ajaxReturn(200, '成功', [
            'greeting' => 'hello,' . $name,
        ]);
    }
}
