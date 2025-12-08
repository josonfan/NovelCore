<?php
namespace app\admin\validate;

use think\Validate;

class SearchConfig extends Validate
{
    protected $scene = [
        'create' => ['site_id','provider','host','index_prefix','username','password','is_active'],
        'update' => ['site_id','provider','host','index_prefix','username','password','is_active','id'],
    ];
    protected $rule = [
        'id' => 'number',
        'site_id' => 'number',
        'provider' => 'require|in:elasticsearch,other',
        'host' => 'require|url',
        'index_prefix' => 'chsDash',
        'username' => 'chsDash',
        'password' => 'chsDash',
        'is_active' => 'in:0,1',
    ];
}

