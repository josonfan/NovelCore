<?php
namespace app\admin\validate;

use think\Validate;

class StorageConfig extends Validate
{
    protected $scene = [
        'create' => ['site_id','provider','access_key_id','secret_key','bucket_name','bucket_region','base_url','is_active'],
        'update' => ['site_id','provider','access_key_id','secret_key','bucket_name','bucket_region','base_url','is_active','id'],
    ];
    protected $rule = [
        'id' => 'number',
        'site_id' => 'number',
        'provider' => 'require|in:b2,s3,other',
        'access_key_id' => 'require',
        'secret_key' => 'require',
        'bucket_name' => 'require',
        'bucket_region' => 'chsDash',
        'base_url' => 'require|url',
        'is_active' => 'in:0,1',
    ];
}

