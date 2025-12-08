<?php
namespace app\admin\validate;

use think\Validate;

class Site extends Validate
{
    protected $scene = [
        'create' => ['name','code','base_api_url','api_token','is_active'],
        'update' => ['name','base_api_url','primary_domain','remark','is_active'],
    ];
    protected $rule = [
        'name' => 'require|chsDash|unique:sites',
        'code' => 'require|alphaDash|unique:sites',
        'base_api_url' => 'require',
        'api_token' => 'require',
        'primary_domain' => 'chsDash',
        'remark' => 'chsDash',
        'is_active' => 'in:0,1',
    ];
    protected $message = [
        'name.require' => '站点名称不能为空',
        'name.chsDash' => '站点名称格式不正确',
        'name.unique' => '站点名称已存在',
        'code.require' => '站点代号不能为空',
        'code.alphaDash' => '站点代号只允许字母数字下划线和破折号',
        'code.unique' => '站点代号已存在',
        'base_api_url.require' => '基础接口地址不能为空',
        'api_token.require' => '站点接口密钥不能为空',
        'is_active.in' => '启用状态仅支持 0 或 1',
    ];
}
