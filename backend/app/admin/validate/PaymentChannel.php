<?php
namespace app\admin\validate;

use think\Validate;

class PaymentChannel extends Validate
{
    protected $scene = [
        'create' => ['name','is_usdt','status','pay_url','limit_price','sort','is_default'],
        'update' => ['name','is_usdt','status','pay_url','limit_price','sort','is_default','remarks','lang','pay_id','skey','md5_key','pay_bankcode','is_web','not_pc','sup_order_url','order_quantity','payment_quantity','place_order','payment','cycle_price','icon_iden'],
        'status' => ['id','status'],
        'toggle' => ['id','status'],
    ];
    protected $rule = [
        'id' => 'require|integer',
        'name' => 'require',
        'is_usdt' => 'in:1,2',
        'status' => 'in:0,1',
        'pay_url' => 'url',
        'sup_order_url' => 'url',
        'limit_price' => 'float',
        'place_order' => 'float',
        'payment' => 'float',
        'cycle_price' => 'float',
        'order_quantity' => 'chsDash',
        'payment_quantity' => 'chsDash',
        'sort' => 'number',
        'is_web' => 'in:1,2',
        'not_pc' => 'in:1,2',
        'is_default' => 'in:1,2',
        'icon_iden' => 'chsDash',
    ];
    protected $message = [
        'id.require' => 'ID不能为空',
        'id.integer' => 'ID必须是整数',
        'name.require' => '渠道名称不能为空',
        'is_usdt.in' => '支付类型仅支持 1(USDT) 或 2(货币)',
        'status.in' => '状态仅支持 0 或 1',
        'pay_url.url' => '接口网址格式不正确',
        'sup_order_url.url' => '补单网址格式不正确',
        'limit_price.float' => '每日收款限额必须为数字',
        'place_order.float' => '下单总额必须为数字',
        'payment.float' => '收款总额必须为数字',
        'cycle_price.float' => '周期统计必须为数字',
        'sort.number' => '排序必须为数字',
        'is_web.in' => '外部浏览器仅支持 1 或 2',
        'not_pc.in' => '不支持PC仅支持 1 或 2',
        'is_default.in' => '是否默认仅支持 1 或 2',
    ];
}
