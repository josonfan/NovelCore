<?php

namespace app\admin\validate;

use think\Validate;

class AppVersions extends Validate
{
    protected $rule = [
        'site_id' => 'require|integer',
        'platform' => 'require|integer|in:1,2',
        'version_code' => 'require|integer',
        'version_name' => 'require|max:30',
        'download_url' => 'require|max:255',
        'is_force' => 'integer|in:0,1',
        'status' => 'integer|in:0,1',
    ];

    protected $message = [
        'site_id.require' => '站点ID不能为空',
        'site_id.integer' => '站点ID必须为整数',
        'platform.require' => '平台类型不能为空',
        'platform.in' => '平台类型无效',
        'version_code.require' => '版本号不能为空',
        'version_code.integer' => '版本号必须为整数',
        'version_name.require' => '版本名称不能为空',
        'version_name.max' => '版本名称不能超过30个字符',
        'download_url.require' => '下载地址不能为空',
        'download_url.max' => '下载地址不能超过255个字符',
    ];

    protected $scene = [
        'save' => ['site_id', 'platform', 'version_code', 'version_name', 'download_url', 'is_force', 'status'],
    ];
}
