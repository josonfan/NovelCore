<?php
namespace app\admin\validate;

use think\Validate;

class Vip extends Validate
{
    protected $scene = [
        'create' => ['name','descript','days','price','old_price','sort','status','is_hot'],
        'update' => ['id','name','descript','days','price','old_price','sort','status','is_hot'],
        'status' => ['id','status'],
    ];
    protected $rule = [
        'id' => 'require|number',
        'name' => 'require|chsDash',
        'descript' => 'chsDash',
        'days' => 'number',
        'price' => 'float',
        'old_price' => 'float',
        'sort' => 'number',
        'status' => 'in:1,0',
        'is_hot' => 'in:1,2',
    ];
    protected $message = [
        'id.require' => 'ID不能为空',
        'name.require' => '卡名称不能为空',
        'name.chsDash' => '卡名称格式不正确',
        'descript.chsDash' => '描述格式不正确',
        'days.number' => '天数必须为数字',
        'price.float' => '金额格式不正确',
        'old_price.float' => '原价格式不正确',
        'sort.number' => '排序必须为数字',
        'status.in' => '状态仅支持 1 或 2',
        'is_hot.in' => '推荐仅支持 1 或 2',
    ];
}
