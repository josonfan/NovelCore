<?php
namespace app\admin\validate;

use think\Validate;

class Tag extends Validate
{
    protected $scene = [
        'create' => ['name','type','is_active'],
        'update' => ['name','type','is_active'],
    ];
    protected $rule = [
        'name' => 'require|chsDash',
        'slug' => 'alphaDash|unique:tags',
        'type' => 'require|in:theme,plot,role,r18,status,other',
        'is_active' => 'in:0,1',
    ];
    protected $message = [
        'name.require' => '标签名称不能为空',
        'name.chsDash' => '标签名称只允许中文、字母、数字、下划线及破折号',
        'slug.alphaDash' => '标签标识只允许字母数字下划线和破折号',
        'slug.unique' => '标签标识已存在',
        'type.require' => '标签类型不能为空',
        'type.in' => '标签类型不合法',
        'is_active.in' => 'is_active 仅支持 0 或 1',
    ];
}
