<?php
namespace app\admin\controller;
use app\admin\service\LoginService;
use app\common\service\JwtService;
class Login extends Backend
{
    // 登录
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
    // 退出登录
    public function logout(){
        LoginService::logout();
        return $this->ajaxReturn(200,'退出成功');
    }
}
