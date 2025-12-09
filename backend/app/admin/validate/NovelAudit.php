<?php
namespace app\admin\validate;

use think\Validate;

class NovelAudit extends Validate
{
    protected $scene = [
        'audit' => ['id','audit_status','audit_remark'],
    ];
    protected $rule = [
        'id' => 'require|number|>:0',
        'audit_status' => 'require|in:0,1,2,3',
        'audit_remark' => 'length:0,255',
    ];
    protected $message = [
        'id.require' => '小说ID不能为空',
        'id.number' => '小说ID必须为数字',
        'id.>' => '小说ID必须大于0',
        'audit_status.require' => '审核状态不能为空',
        'audit_status.in' => '审核状态不合法',
        'audit_remark.length' => '审核备注长度不合法',
    ];
}
