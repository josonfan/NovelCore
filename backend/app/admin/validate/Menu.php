<?php
namespace app\admin\validate;

use think\Validate;

class Menu extends Validate
{
    protected $scene = [
        'create' => ['name','code','type','visible','is_active','sort_order'],
        'update' => ['name','type','visible','is_active','sort_order'],
    ];
    protected $rule = [
        'name' => 'require|chsDash',
        'code' => 'require|alphaDash|unique:menus',
        'type' => 'require|in:menu,button',
        'visible' => 'in:0,1',
        'is_active' => 'in:0,1',
        'sort_order' => 'number',
    ];
    protected $message = [
        'name.require' => '菜单名称不能为空',
        'name.chsDash' => '菜单名称只允许中文、字母、数字、下划线及破折号',
        'code.require' => '菜单编码不能为空',
        'code.alphaDash' => '菜单编码只允许字母数字下划线和破折号',
        'code.unique' => '菜单编码已存在',
        'type.in' => '类型仅支持 menu 或 button',
        'visible.in' => 'visible 仅支持 0 或 1',
        'is_active.in' => 'is_active 仅支持 0 或 1',
        'sort_order.number' => '排序必须为数字',
    ];
}

