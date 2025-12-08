<?php
namespace app\admin\controller;
use app\admin\service\LoginService;
use app\admin\service\AdminsService;
use app\common\service\JwtService;
class Login extends Backend
{
    /**
     * 登录
     * @param array $data 登录数据
     * @return array|false
     */
    public function login()
    {
        $postField = 'username,password';
		$data = $this->request->only(explode(',',$postField),'post',null);
        $user = LoginService::login($data);
        if($user){
            $ttl = (int)env('JWT.TTL', 3600);
            $token = JwtService::encode(['uid'=>$user['id']], $ttl, (string)$user['id'], env('JWT.AUDIENCE',''));
            return $this->ajaxReturn(200,'登录成功',$user,$token);
        }
        return $this->ajaxReturn(400,'登录失败');
    }
    /**
     * 个人信息
     * @return array|false
     */
    public function info(){
        $uid = (int)($this->request->uid ?? 0);
        $info = AdminsService::infoById($uid, 'id,username,nickname,last_login_at,status,created_at');
        if (empty($info)) {
            return $this->ajaxReturn(404,'用户不存在');
        }
        return $this->ajaxReturn(200,'成功',$info);
    }
    /**
     * 编辑用户信息
     * @param array $data 编辑数据
     * @return array|false
     */
    public function update(){
        $postField = 'nickname';
		$data = $this->request->only(explode(',',$postField),'post',null);
        $uid = $this->request->uid;
        $ok = AdminsService::edit($uid, $data);
        if($ok){
            return $this->ajaxReturn(200,'更新成功');
        }
        return $this->ajaxReturn(400,'更新失败');
    }
    /** 修改密码 */
    public function changePassword(){
        $postField = 'password,new_password,confirm_password';
		$data = $this->request->only(explode(',',$postField),'post',null);
        $uid = $this->request->uid;
        $ok = AdminsService::changePassword($uid, $data);
        if($ok){
            return $this->ajaxReturn(200,'更新成功');
        }
        return $this->ajaxReturn(400,'更新失败');
    }
    /**
     * 退出登录
     * @return array|false
     */
    public function logout(){
        LoginService::logout();
        return $this->ajaxReturn(200,'退出成功');
    }
    /**
     * 登录后上下文：用户信息 + 角色ID + 权限 + 菜单树
     * @return array|false
     */
    public function context(){
        $uid = (int)($this->request->uid ?? 0);
        $user = AdminsService::infoById($uid, 'id,username,nickname,last_login_at,status,created_at');
        if (empty($user)) {
            return $this->ajaxReturn(404,'用户不存在');
        }
        $roles = \app\admin\model\AdminRole::where('admin_id', $uid)->column('role_id');
        $perms = \app\admin\service\RbacService::getAdminPermissions($uid);
        $menus = \app\admin\service\MenuService::treeForAdmin($uid);
        return $this->ajaxReturn(200,'成功',[ 'user'=>$user, 'roles'=>$roles, 'permissions'=>$perms, 'menus'=>$menus ]);
    }
}
