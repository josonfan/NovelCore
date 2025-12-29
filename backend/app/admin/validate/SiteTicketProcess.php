<?php
namespace app\admin\validate;

use think\Validate;

class SiteTicketProcess extends Validate
{
    protected $scene = [
        'process' => ['id','status','reply_content'],
    ];
    protected $rule = [
        'id' => 'require|number|>:0',
        'status' => 'require|in:0,1,2,3,4',
        'reply_content' => 'length:0,2000',
    ];
    protected $message = [
        'id.require' => '工单ID不能为空',
        'id.number' => '工单ID必须为数字',
        'id.>' => '工单ID必须大于0',
        'status.require' => '状态不能为空',
        'status.in' => '状态不合法',
        'reply_content.length' => '回复长度不合法',
    ];
}
