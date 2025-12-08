<?php
namespace app\admin\validate;

use think\Validate;

class Domain extends Validate
{
    protected $scene = [
        'create' => ['site_id','domain','type','priority','is_active'],
        'update' => ['site_id','domain','type','priority','is_active','remark'],
    ];
    protected $rule = [
        'site_id' => 'number',
        'domain' => 'require',
        'type' => 'require|in:api,image,share',
        'priority' => 'number',
        'is_active' => 'in:0,1',
        'remark' => 'chsDash',
    ];
    protected $message = [
        'domain.require' => '域名不能为空',
        'type.require' => '域名类型不能为空',
        'type.in' => '域名类型仅支持 api/image/share',
        'priority.number' => '优先级必须为数字',
        'is_active.in' => '启用状态仅支持 0 或 1',
    ];
}

