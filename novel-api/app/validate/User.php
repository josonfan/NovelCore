<?php
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $scene = [
        'register' => ['username', 'password', 'confirm_password', 'email'],
        'login' => ['username', 'password'],
    ];
    
    protected $rule = [
        'username' => 'require|unique:users,username|length:4,20',
        'password' => 'require|confirm:confirm_password|length:6,20',
        'email' => 'email|unique:users,email',
    ];
    
    protected $message = [
        'username.require' => '用户名不能为空',
        'username.unique' => '用户名已存在',
        'username.length' => '用户名长度需在4-20个字符之间',
        'password.require' => '密码不能为空',
        'password.confirm' => '两次输入密码不一致',
        'password.length' => '密码长度需在6-20个字符之间',
        'email.require' => '邮箱不能为空',
        'email.email' => '邮箱格式错误',
        'email.unique' => '邮箱已存在',
    ];
}
