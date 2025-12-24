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
     * 入参：orderType（chapter|novel|vip）、good_id
     * 返回：data { order_no }
     */
    public function add()
    {
        $userId = (int)($this->request->user_id ?? 0);
        $orderType = (string)$this->request->post('orderType', 'vip');
        $goodId = (int)$this->request->post('good_id', 0);
        if ($goodId <= 0) {
            throw new ValidateException('商品ID不能为空');
        }
        
        $res = OrderService::add($userId, $orderType, $goodId);
        return $this->ajaxReturn(200, '保存成功', $res);
    }

    /**
     * 确认购买
     * 路由：POST /api/Order/affirmBuy
     * 鉴权：需登录
     * 入参：order_no
     * 返回：data { order_no,pay_channel_id }
     */
    public function affirmBuy()
    {
        $userId = (int)($this->request->user_id ?? 0);
        $orderNo = (string)$this->request->post('order_no', '');
        $payChannelId = (int)$this->request->post('pay_channel_id', 0);
        $res = OrderService::affirmBuy($userId, $orderNo, $payChannelId);   
        return $this->ajaxReturn(200, '获取成功', $res);
    }
    public function info()
    {
        $userId = (int)($this->request->user_id ?? 0);
        $orderNo = (string)$this->request->post('order_no', '');
        $res = OrderService::info($userId, $orderNo);
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    /**
     * 支付回调
     * 路由：POST /api/Order/notify/:icon_iden/:id
     * 鉴权：无需登录（支付渠道回调）
     * 入参：order_no,status,transaction_id,amount,channel,...
     * 返回：data { saved: true, status }
     */
    public function notify()
    {
        $data = $this->_data ?? [];
        unset($data['timestamp']);
        if(empty($data))$data=$this->request->param();
        if(empty( $data['icon_iden'])||empty($data['channel_id'])){
            return response('error', 200)->contentType('text/plain');
        }
        $res = OrderService::notify((array)$data);
        $msg = (string)($res['ok_msg'] ?? 'error');
        return response($msg, 200)->contentType('text/plain');
    }
    
}
