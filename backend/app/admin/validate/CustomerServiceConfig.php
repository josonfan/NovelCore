<?php
namespace app\admin\validate;

use think\Validate;

class CustomerServiceConfig extends Validate
{
    protected $scene = [
        'create' => ['site_id','support_email','support_url','appid','is_active','remark'],
        'update' => ['site_id','support_email','support_url','appid','is_active','remark','id'],
    ];
    protected $rule = [
        'id' => 'number',
        'site_id' => 'number',
        'support_email' => 'email',
        'support_url' => 'url',
        'appid' => 'chsDash',
        'is_active' => 'in:0,1',
        'remark' => 'chsDash',
    ];
}

