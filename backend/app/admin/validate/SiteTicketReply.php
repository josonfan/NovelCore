<?php
namespace app\admin\validate;

use think\Validate;

class SiteTicketReply extends Validate
{
    protected $scene = [
        'reply' => ['id', 'content','status'],
    ];
    
    protected $rule = [
        'id' => 'require|number|>:0',
        'content' => 'require|length:1,5000',
        'status' => 'require|number|in:0,1,3',
    ];
    
    protected $message = [
        'id.require' => '工单ID不能为空',
        'id.number' => '工单ID必须为数字',
        'id.>' => '工单ID必须大于0',
        'content.require' => '回复内容不能为空',
        'content.length' => '回复内容长度不合法',
        'status.require' => '工单状态不能为空',
        'status.number' => '工单状态必须为数字',
        'status.in' => '工单状态不合法',
    ];
}
