<?php
declare(strict_types=1);

namespace app\controller;
use app\service\UserAuthService;
use app\service\UserService;

class User extends Common
{
    /**
     * 用户注册
     * 路由：POST /api/User/register
     * 鉴权：无需登录
     * 入参：username, password, confirm_password, email?
     * 返回：data { uid }
     */
    public function register()
    {
        $postField = 'username,password,confirm_password,email';
		$data = $this->request->only(explode(',',$postField),'post',null);
        $res = UserAuthService::register($data);
        return $this->ajaxReturn(200, '注册成功', $res);
    }

    /**
     * 用户登录
     * 路由：POST /api/User/login
     * 鉴权：无需登录
     * 入参：username, password
     * 返回：data { token, token_expire }
     */
    public function login()
    {
        $postField = 'username,password';
		$data = $this->request->only(explode(',',$postField),'post',null);
        $field = 'id,username,email,nickname,avatar,status';
        $res = UserAuthService::login($data,$field);
        return $this->ajaxReturn(200, '登录成功', $res);
    }

    /**
     * 退出登录
     * 路由：POST /api/User/logout
     * 鉴权：需登录
     * 入参：无
     * 返回：code=200
     */
    public function logout()
    {
        $ok = UserAuthService::logout();
        return $this->ajaxReturn(200, '退出成功', ['ok' => $ok]); 
    }

    /**
     * 用户信息
     * 路由：GET /api/User/info
     * 鉴权：需登录
     * 入参：无
     * 返回：data 用户主表/视图字段
     */
    public function info()
    {
        $userId = $this->request->user_id;
        $field = 'id,username,email,nickname,avatar,vip_expire,status';
        $user = UserService::info($userId,$field);
        $user['uid'] = getUidByID((int)($user['id'] ?? $userId));
        unset($user['id']);
        return $this->ajaxReturn(200, '用户信息', $user);
    }

    /**
     * 编辑用户信息
     * 路由：POST /api/User/update
     * 鉴权：需登录
     * 入参：nickname?, email?, avatar?
     * 返回：data 用户主表/视图字段
     */
    public function update()
    {
        $userId = (int)($this->request->user_id ?? 0);
        $postField = 'nickname,email,avatar';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = UserService::update($userId, $data);
        return $this->ajaxReturn(200, '保存成功', $res);
    }
}
