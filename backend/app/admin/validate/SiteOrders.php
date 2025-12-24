<?php
namespace app\admin\validate;

use think\Validate;

class SiteOrders extends Validate
{
    protected $scene = [
        'update' => ['id','order_type','amount','pay_channel_id','status','extra','paid_at','good_id','good_info'],
    ];
    protected $rule = [
        'id' => 'require|integer',
        'order_type' => 'in:chapter,novel,vip',
        'amount' => 'float',
        'pay_channel_id' => 'number',
        'good_id' => 'number',
        'status' => 'in:pending,paid,cancelled,refunded',
        'paid_at' => 'date',
    ];
    protected $message = [
        'id.require' => 'ID不能为空',
        'id.integer' => 'ID必须是整数',
        'order_type.in' => '订单类型不合法',
        'amount.float' => '支付金额格式不正确',
        'pay_channel_id.number' => '支付渠道ID必须为数字',
        'good_id.number' => '商品ID必须为数字',
        'status.in' => '订单状态不合法',
        'paid_at.date' => '支付完成时间格式不正确',
    ];
}
