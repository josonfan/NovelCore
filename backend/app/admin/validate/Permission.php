<?php
namespace app\admin\validate;

use think\Validate;

class Permission extends Validate
{
    protected $scene = [
        'create' => ['name','resource','action'],
    ];
    protected $rule = [
        'name' => 'require|chsDash',
        'resource' => 'require|alphaDash',
        'action' => 'require|alphaDash',
        'field' => 'alphaDash',
    ];
    protected $message = [
        'name.require' => '权限名称不能为空',
        'name.chsDash' => '权限名称只允许中文、字母、数字、下划线及破折号',
        'resource.require' => '资源名称不能为空',
        'resource.alphaDash' => '资源名称只允许字母数字下划线和破折号',
        'action.require' => '操作名称不能为空',
        'action.alphaDash' => '操作名称只允许字母数字下划线和破折号',
        'field.alphaDash' => '字段名称只允许字母数字下划线和破折号',
    ];
}

