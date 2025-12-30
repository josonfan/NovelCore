<?php
namespace app\admin\validate;

use think\Validate;

class SiteFeedbacks extends Validate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'status' => 'require|in:0,1,2,3,4',
        'reply_content' => 'max:2000'
    ];

    protected $message = [
        'id.require' => 'ID不能为空',
        'id.integer' => 'ID必须为整数',
        'status.require' => '状态不能为空',
        'status.in' => '状态值无效',
        'reply_content.max' => '回复内容不能超过2000个字符'
    ];

    protected $scene = [
        'process' => ['id', 'status', 'reply_content'],
    ];
}
