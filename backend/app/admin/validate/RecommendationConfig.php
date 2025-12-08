<?php
namespace app\admin\validate;

use think\Validate;

class RecommendationConfig extends Validate
{
    protected $scene = [
        'create' => ['site_id','provider','api_url','api_key','is_active'],
        'update' => ['site_id','provider','api_url','api_key','is_active','id'],
    ];
    protected $rule = [
        'id' => 'number',
        'site_id' => 'number',
        'provider' => 'require|in:gorse,other',
        'api_url' => 'require|url',
        'api_key' => 'chsDash',
        'is_active' => 'in:0,1',
    ];
}

