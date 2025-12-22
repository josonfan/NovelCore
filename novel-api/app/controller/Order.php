<?php
declare(strict_types=1);

namespace app\controller;

use app\service\OrderService;
use think\exception\ValidateException;

class Order extends Common
{
    /**
     * 创建订单
     * 路由：POST /api/Order/add
     * 鉴权：需登录
     * 入参：orderType（chapter|novel|vip）、amount、novel_id?、chapter_id?、vip_plan?、pay_channel?
     * 返回：data { order_no }
     */
    public function add()
    {
        $userId = (int)($this->request->user_id ?? 0);
        $orderType = (string)$this->request->post('orderType', '');
        $payload = [
            'amount'      => (float)$this->request->post('amount', 0),
            'novel_id'    => (int)$this->request->post('novel_id', 0),
            'chapter_id'  => (int)$this->request->post('chapter_id', 0),
            'vip_plan'    => (string)$this->request->post('vip_plan', ''),
            'pay_channel' => (string)$this->request->post('pay_channel', ''),
            'client'      => (string)$this->request->header('user-agent', ''),
        ];
        $res = OrderService::add($userId, $orderType, $payload);
        return $this->ajaxReturn(200, '保存成功', $res);
    }

    /**
     * 确认购买
     * 路由：POST /api/Order/affirmBuy
     * 鉴权：需登录
     * 入参：order_no
     * 返回：data { order_no,status,amount,channel,pay_params }
     */
    public function affirmBuy()
    {
        $userId = (int)($this->request->user_id ?? 0);
        $orderNo = (string)$this->request->post('order_no', '');
        $res = OrderService::affirmBuy($userId, $orderNo);
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    /**
     * 支付回调
     * 路由：POST /api/Order/notify
     * 鉴权：无需登录（支付渠道回调）
     * 入参：order_no,status,transaction_id,amount,channel,...
     * 返回：data { saved: true, status }
     */
    public function notify()
    {
        $payload = $this->_data ?? [];
        $res = OrderService::notify((array)$payload);
        return $this->ajaxReturn(200, '保存成功', $res);
    }
}
