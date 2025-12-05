<?php
namespace app\admin\validate;

use think\Validate;

class Role extends Validate
{
    protected $scene = [
        'create' => ['name'],
    ];
    protected $rule = [
        'name' => 'require|alphaDash|unique:roles',
        'description' => 'chsDash',
    ];
    protected $message = [
        'name.require' => '角色名称不能为空',
        'name.alphaDash' => '角色名称只允许字母数字下划线和破折号',
        'name.unique' => '角色名称已存在',
        'description.chsDash' => '描述只允许中文、字母、数字、下划线及破折号',
    ];
}
