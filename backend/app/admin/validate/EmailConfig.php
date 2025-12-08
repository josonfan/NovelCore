<?php
namespace app\admin\validate;

use think\Validate;

class EmailConfig extends Validate
{
    protected $scene = [
        'create' => ['site_id','provider','region','access_key','secret_key','from_address','from_name','is_active'],
        'update' => ['site_id','provider','region','access_key','secret_key','from_address','from_name','is_active','id'],
    ];
    protected $rule = [
        'id' => 'number',
        'site_id' => 'number',
        'provider' => 'require|in:ses,smtp,other',
        'region' => 'chsDash',
        'access_key' => 'require',
        'secret_key' => 'require',
        'from_address' => 'require|email',
        'from_name' => 'chsDash',
        'is_active' => 'in:0,1',
    ];
}

