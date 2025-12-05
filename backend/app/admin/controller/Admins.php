<?php
namespace app\admin\controller;
use app\admin\service\AdminsService;
use app\admin\service\RbacService;
class Admins extends Backend
{
    // 列表
    public function index(){
        
    }
    // 详情
    public function add()
    {
        $postField = 'username,password';
		$data = $this->request->only(explode(',',$postField),'post',null);
        $user = AdminsService::add($data);
        return $this->ajaxReturn(200,'添加成功',$user);
    }
    public function assignRoles()
    {
        $postField = 'admin_id,role_ids';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $adminId = (int)($data['admin_id'] ?? 0);
        $roleIds = array_filter(array_map('intval', explode(',', (string)($data['role_ids'] ?? ''))));
        RbacService::assignRoles($adminId, $roleIds);
        return $this->ajaxReturn(200, '绑定成功');
    }
}
