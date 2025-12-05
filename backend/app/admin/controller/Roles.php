<?php
namespace app\admin\controller;

use app\admin\service\RbacService;

class Roles extends Backend
{
    public function create()
    {
        $postField = 'name,description';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $role = RbacService::createRole($data);
        return $this->ajaxReturn(200, '创建成功', $role);
    }

    public function assignPermissions()
    {
        $postField = 'role_id,perm_ids';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $roleId = (int)($data['role_id'] ?? 0);
        $permIds = array_filter(array_map('intval', explode(',', (string)($data['perm_ids'] ?? ''))));
        RbacService::assignPermissions($roleId, $permIds);
        return $this->ajaxReturn(200, '绑定成功');
    }

    public function index()
    {
        $list = RbacService::roles();
        return $this->ajaxReturn(200, '成功', $list);
    }
}

