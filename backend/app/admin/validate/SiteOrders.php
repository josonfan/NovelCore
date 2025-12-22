<?php
namespace app\admin\validate;

use think\Validate;

class SiteOrders extends Validate
{
    protected $scene = [
        'update' => ['id','order_type','vip_plan','amount','pay_channel','status','extra','site_paid_at'],
    ];
    protected $rule = [
        'id' => 'require|integer',
        'order_type' => 'in:chapter,novel,vip',
        'vip_plan' => 'in:month,quarter,year',
        'amount' => 'float',
        'pay_channel' => 'alphaDash',
        'status' => 'in:pending,paid,cancelled,refunded',
        'extra' => 'chsDash',
        'site_paid_at' => 'date',
    ];
    protected $message = [
        'id.require' => 'ID不能为空',
        'id.integer' => 'ID必须是整数',
        'order_type.in' => '订单类型不合法',
        'vip_plan.in' => 'VIP套餐不合法',
        'amount.float' => '支付金额格式不正确',
        'pay_channel.alphaDash' => '支付渠道仅支持字母数字下划线和破折号',
        'status.in' => '订单状态不合法',
        'site_paid_at.date' => '支付完成时间格式不正确',
    ];
}

