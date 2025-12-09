<?php
namespace app\admin\validate;

use think\Validate;

class Category extends Validate
{
    protected $scene = [
        'create' => ['name','sort_order','is_active'],
        'update' => ['name','sort_order','is_active'],
    ];
    protected $rule = [
        'name' => 'require|chsDash',
        'slug' => 'alphaDash|unique:categories',
        'sort_order' => 'number',
        'is_active' => 'in:0,1',
    ];
    protected $message = [
        'name.require' => '分类名称不能为空',
        'name.chsDash' => '分类名称只允许中文、字母、数字、下划线及破折号',
        'slug.alphaDash' => '分类标识只允许字母数字下划线和破折号',
        'slug.unique' => '分类标识已存在',
        'sort_order.number' => '排序必须为数字',
        'is_active.in' => 'is_active 仅支持 0 或 1',
    ];
}
