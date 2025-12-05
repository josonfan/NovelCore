<?php
namespace app\admin\validate;
use think\Validate;

class Admins extends Validate
{
    // 登录场景
    protected $scene = [
        'add' => ['username','password'],
    ];
    // 登录场景规则
    protected $rule = [
        'username' => 'require|alphaNum|unique:admins',
        'password' => 'require|alphaNum',
    ];
    // 登录场景提示
    protected $message = [
        'username.require' => '用户名不能为空',
        'username.unique' => '用户名已存在',
        'username.alphaNum' => '用户名只能包含字母和数字',
        'password.require' => '密码不能为空',
        'password.alphaNum' => '密码只能包含字母和数字',
    ];
}