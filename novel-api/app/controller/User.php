<?php
declare(strict_types=1);

namespace app\controller;
use app\service\UserAuthService;
use app\service\UserService;
use app\model\User as UserModel;

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
     * 发送忘记密码验证码
     * 路由：POST /api/User/sendForgotPasswordCode
     * 鉴权：无需登录
     * 入参：email
     * 返回：code=200
     */
    public function sendForgotPasswordCode()
    {
        
        $username = $this->request->param('username', '', 'trim');
        if (empty($username)) {
            return $this->ajaxReturn(400, '用户名不能为空');
        }
        $email = UserModel::where('username', $username)->value('email');
        if (empty($email)) {
            return $this->ajaxReturn(400, '该用户名未绑定邮箱');
        }
        \app\service\MailService::sendVerificationCode((string)$email, 'reset_pwd');
        return $this->ajaxReturn(200, '验证码已发送');
    }

    /**
     * 重置密码
     * 路由：POST /api/User/resetPassword
     * 鉴权：无需登录
     * 入参：email, code, password, confirm_password
     * 返回：code=200
     */
    public function resetPassword()
    {
        $username = $this->request->param('username', '', 'trim');
        $password = $this->request->param('password', '', 'trim');
        $confirmPassword = $this->request->param('confirm_password', '', 'trim');
        
        // code 已经在中间件验证过了

        if (empty($password) || empty($confirmPassword)) {
            return $this->ajaxReturn(400, '密码不能为空');
        }
        if ($password !== $confirmPassword) {
            return $this->ajaxReturn(400, '两次密码输入不一致');
        }
        $email = UserService::getEmail($username);
        UserAuthService::resetPassword($email, $password);
        
        return $this->ajaxReturn(200, '密码重置成功');
    }

    /**
     * 发送邮箱验证码
     * 路由：POST /api/User/sendEmailCode
     * 鉴权：需登录
     * 入参：email
     * 返回：code=200
     */
    public function sendEmailCode()
    {
        $email = $this->request->param('email', '', 'trim');
        if (empty($email)) {
            return $this->ajaxReturn(400, '邮箱不能为空');
        }
        \app\service\MailService::sendVerificationCode($email, 'bind');
        return $this->ajaxReturn(200, '验证码已发送');
    }

    /**
     * 绑定邮箱
     * 路由：POST /api/User/bindEmail
     * 鉴权：需登录
     * 入参：email, code
     * 返回：code=200
     */
    public function bindEmail()
    {
        $userId = (int)$this->request->user_id;
        $email = $this->request->param('email', '', 'trim');
        // code 已经在中间件验证过了

        // 检查邮箱是否已绑定
        $user = \app\model\User::where('email', $email)->find();
        if ($user && (int)$user['id'] !== $userId) {
             return $this->ajaxReturn(400, '该邮箱已绑定');
        }
        
        // 更新用户邮箱
        UserService::update($userId, ['email' => $email]);
        
        return $this->ajaxReturn(200, '邮箱绑定成功');
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
    /**
     * 编辑用户密码
     * 路由：POST /api/User/updatePassword
     * 鉴权：需登录
     * 入参：password, confirm_password
     * 返回：code=200
     */
    public function updatePassword()
    {
        $userId = (int)($this->request->user_id ?? 0);
        $password = $this->request->param('password', '', 'trim');
        $confirmPassword = $this->request->param('confirm_password', '', 'trim');
        if (empty($password) || empty($confirmPassword)) {
            return $this->ajaxReturn(400, '密码不能为空');
        }
        if ($password !== $confirmPassword) {
            return $this->ajaxReturn(400, '两次密码输入不一致');
        }
        UserAuthService::updatePassword($userId, $password);
        return $this->ajaxReturn(200, '密码更新成功');
    }

    /**
     * 用户登录日志
     * 路由：GET /api/User/loginLogs
     * 鉴权：需登录
     * 入参：无
     * 返回：data 登录日志列表
     */
    public function getLoginLogs()
    {
        $userId = (int)($this->request->user_id ?? 0);
        $page = $this->request->param('page', 1, 'intval');
        $pageSize = $this->request->param('page_size', 10, 'intval');
        $logs = UserService::getLoginLogs($userId, $page, $pageSize);
        return $this->ajaxReturn(200, '登录日志', $logs);
    }

    /**
     * 用户登录日志
     * 路由：GET /api/User/deviceLogs
     * 鉴权：需登录
     * 入参：无
     * 返回：data 登录日志列表
     */
    public function getDeviceLogs()
    {
        $userId = (int)($this->request->user_id ?? 0);
        $page = $this->request->param('page', 1, 'intval');
        $pageSize = $this->request->param('page_size', 10, 'intval');
        $logs = UserService::getDeviceLogs($userId, $page, $pageSize);
        return $this->ajaxReturn(200, '登录日志', $logs);
    }
    //账户查询邮箱 注意邮箱部分隐藏 忘记密码配套使用
    /**
     * 用户查询邮箱
     * 路由：POST /api/User/getEmail
     * 鉴权：无需登录
     * 入参：username
     * 返回：data 邮箱
     */
    public function getEmail()
    {
        $username = $this->request->param('username', '', 'trim');
        if (empty($username)) {
            throw new \think\exception\ValidateException('用户名不能为空');
        }
        $email = UserService::getEmail($username);
        
        // 邮箱脱敏处理
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $parts = explode('@', $email);
            $name = $parts[0];
            $domain = $parts[1];
            if (mb_strlen($name) > 2) {
                $name = mb_substr($name, 0, 2) . '****' . mb_substr($name, -1);
            } else {
                $name = mb_substr($name, 0, 1) . '****';
            }
            $email = $name . '@' . $domain;
        } else {
            $email = substr($email, 0, 3) . '****' . substr($email, -4);
        }
        
        return $this->ajaxReturn(200, '返回成功', $email);
    }
    
}
