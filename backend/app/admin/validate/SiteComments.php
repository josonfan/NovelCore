<?php
namespace app\admin\validate;

use think\Validate;

class SiteComments extends Validate
{
    protected $rule = [
        'id' => 'require|integer',
        'status' => 'require|in:0,1,2',
        'audit_reason' => 'max:255',
    ];

    protected $message = [
        'id.require' => 'ID不能为空',
        'id.integer' => 'ID必须是整数',
        'status.require' => '状态不能为空',
        'status.in' => '状态值无效(0=待审核,1=通过,2=拒绝)',
        'audit_reason.max' => '审核原因不能超过255个字符',
    ];

    protected $scene = [
        'audit' => ['id', 'status', 'audit_reason'],
    ];
}
