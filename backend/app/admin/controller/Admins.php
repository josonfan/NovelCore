<?php
namespace app\admin\controller;
use app\admin\service\AdminsService;
use app\admin\service\RbacService;
class Admins extends Backend
{
    // 列表
    public function index(){
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];        
        $field = 'id,username,nickname,last_login_at,status,created_at';
        $orderby = 'id desc';
        $m = new \app\admin\model\Admins();
        $list = $m->listAdmins(formatWhere($where),$field,$orderby,$limit,$page);
        return $this->ajaxReturn(200,'成功',$list);
    }
    // 个人信息
    public function info(){
        $uid = (int)($this->request->uid ?? 0);
        $m = new \app\admin\model\Admins();
        $info = $m->infoById($uid, 'id,username,nickname,last_login_at,status,created_at');
        if (empty($info)) {
            return $this->ajaxReturn(404,'用户不存在');
        }
        return $this->ajaxReturn(200,'成功',$info);
    }
    // 详情
    public function detail(){
        $id = (int)($this->request->param('id') ?? 0);
        $m = new \app\admin\model\Admins();
        $info = $m->infoById($id, 'id,username,nickname,last_login_at,status,created_at');
        if (empty($info)) {
            return $this->ajaxReturn(404,'用户不存在');
        }
        return $this->ajaxReturn(200,'成功',$info);
    }
    // 添加
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
