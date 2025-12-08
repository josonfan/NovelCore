<?php
namespace app\admin\validate;

use think\Validate;

class TelegramAuditConfig extends Validate
{
    protected $scene = [
        'create' => ['site_id','bot_token','chat_id','is_active','remark'],
        'update' => ['site_id','bot_token','chat_id','is_active','remark','id'],
    ];
    protected $rule = [
        'id' => 'number',
        'site_id' => 'number',
        'bot_token' => 'require',
        'chat_id' => 'require|number',
        'is_active' => 'in:0,1',
        'remark' => 'chsDash',
    ];
}

