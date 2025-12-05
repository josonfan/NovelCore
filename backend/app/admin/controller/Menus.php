<?php
namespace app\admin\controller;

use app\admin\service\MenuService;
use app\common\service\JwtService;

class Menus extends Backend
{
    public function index()
    {
        $token = $this->request->header('token') ?: $this->request->header('authorization');
        $claims = JwtService::decode((string)$token);
        $uid = (int)($claims['uid'] ?? 0);
        $tree = MenuService::treeForAdmin($uid);
        return $this->ajaxReturn(200, '成功', $tree);
    }
}

