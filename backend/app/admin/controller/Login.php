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
            $token = JwtService::encode(['uid'=>$user['id']], 3600, (string)$user['id'], env('JWT.AUDIENCE',''));
            return $this->ajaxReturn(200,'登录成功',$user,$token);
        }
        return $this->ajaxReturn(400,'登录失败');
    }
}
