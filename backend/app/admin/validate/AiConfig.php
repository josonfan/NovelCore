<?php
namespace app\admin\validate;

use think\Validate;

class AiConfig extends Validate
{
    protected $scene = [
        'create' => ['site_id','provider','api_key','api_base_url','model','is_active'],
        'update' => ['site_id','provider','api_key','api_base_url','model','is_active','id'],
    ];
    protected $rule = [
        'id' => 'number',
        'site_id' => 'number',
        'provider' => 'require|in:openai,other',
        'api_key' => 'require',
        'api_base_url' => 'url',
        'model' => 'chsDash',
        'is_active' => 'in:0,1',
    ];
}

