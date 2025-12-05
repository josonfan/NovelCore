<?php
namespace app\admin\controller;

use app\admin\service\RbacService;

class Permissions extends Backend
{
    public function create()
    {
        $postField = 'name,resource,action,field';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $perm = RbacService::createPermission($data);
        return $this->ajaxReturn(200, '创建成功', $perm);
    }

    public function index()
    {
        $list = RbacService::permissions();
        return $this->ajaxReturn(200, '成功', $list);
    }
}

