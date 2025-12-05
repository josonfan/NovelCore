<?php
namespace app\admin\service;

use think\exception\ValidateException;
use think\facade\Db;
use app\admin\model\Admins;
use think\Exception;
use think\facade\Log;
class LoginService
{
    // 登录
    public static function login($data){
        try{
            validate(\app\admin\validate\Login::class)->scene('login')->check($data);
            $username = $data['username'];
            $password = $data['password'];
            $model = new Admins();
            // 验证用户名和密码是否正确
            $user = $model->where('username', $username)->find();
            if (!$user) throw new ValidateException('账户不存在');
            if (!password_verify($password, $user['password'])) throw new ValidateException('用户名或密码错误');
            $user->last_login_at = date('Y-m-d H:i:s');
            $model->writeById((int)$user['id'], ['last_login_at' => $user->last_login_at], 600);
            $user = getArrayByFields($user,'id,username,nickname,last_login_at,status,created_at');
            // 登录成功
            return $user;
        }catch(ValidateException $e){
            throw new ValidateException ($e->getError());
        }catch(\Exception $e){
            throw new \Exception ($e->getMessage());
        }
    }
}
