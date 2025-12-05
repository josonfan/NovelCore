<?php
namespace app\admin\validate;
use think\Validate;
class Login extends Validate
{    
    // 登录场景
    protected $scene = [
        'login' => ['username','password'],
    ];
    // 登录场景规则
    protected $rule = [
        'username' => 'require|alphaNum',
        'password' => 'require|alphaNum',
    ];
    // 登录场景提示信息
    protected $message = [
        'username.require' => '用户名不能为空',
        'username.alphaNum' => '用户名只能包含字母和数字',
        'password.require' => '密码不能为空',
        'password.alphaNum' => '密码只能包含字母和数字',
    ];
}