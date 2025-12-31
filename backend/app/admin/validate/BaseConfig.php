<?php
namespace app\admin\validate;

use think\Validate;

class BaseConfig extends Validate
{
    protected $scene = [
        'save' => ['site_id', 'config_name', 'config_data'],
    ];

    protected $rule = [
        'site_id' => 'require|integer',
        'config_name' => 'require|max:50',
        'config_data' => 'array',
    ];

    protected $message = [
        'site_id.require' => '站点ID不能为空',
        'config_name.require' => '配置名称不能为空',
        'config_data.array' => '配置数据必须是数组/JSON对象',
    ];
}
