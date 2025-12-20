<?php
namespace app\admin\validate;

use think\Validate;

class SiteUsers extends Validate
{
    protected $rule = [
        'id' => 'require|integer',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'id.require' => 'ID不能为空',
        'id.integer' => 'ID必须是整数',
        'status.require' => '状态不能为空',
        'status.in' => '状态值无效',
    ];

    protected $scene = [
        'status' => ['id', 'status'],
    ];
}
