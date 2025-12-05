<?php
namespace app\admin\validate;

use think\Validate;

class AdminRole  extends Validate
{
    // 定义验证规则
    protected $rule = [
        'admin_id' => 'require|number',
        'role_ids' => 'require|array',
    ];
    // 定义验证提示
    protected $message = [
        'admin_id.require' => '管理员ID不能为空',
        'admin_id.number' => '管理员ID必须是数字',
        'role_ids.require' => '角色ID数组不能为空',
        'role_ids.array' => '角色ID数组必须是数组',
    ];
}