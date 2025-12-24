<?php
namespace app\admin\controller;

use app\admin\service\SiteOrdersService;

class SiteOrders extends Backend
{
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $siteId = $this->request->param('site_id');
        if ($siteId !== null && $siteId !== '') {
            $where['site_id'] = (int)$siteId;
        }
        $userId = $this->request->param('user_id');
        if ($userId !== null && $userId !== '') {
            $where['user_id'] = (int)$userId;
        }
        $orderNo = $this->request->param('order_no');
        if (!empty($orderNo)) {
            $where['order_no'] = $orderNo;
        }
        $status = $this->request->param('status');
        if ($status !== null && $status !== '') {
            $where['status'] = (string)$status;
        }
        $orderType = $this->request->param('order_type');
        if ($orderType !== null && $orderType !== '') {
            $where['order_type'] = (string)$orderType;
        }
        $orderId = $this->request->param('order_id');
        if ($orderId !== null && $orderId !== '') {
            $where['order_id'] = (int)$orderId;
        }
        $goodId = $this->request->param('good_id');
        if ($goodId !== null && $goodId !== '') {
            $where['good_id'] = (int)$goodId;
        }
        $payChannelId = $this->request->param('pay_channel_id');
        if ($payChannelId !== null && $payChannelId !== '') {
            $where['pay_channel_id'] = (int)$payChannelId;
        }
        $field = 'id,site_id,order_id,order_no,user_id,order_type,good_id,good_info,amount,pay_channel_id,status,extra,created_at,updated_at,paid_at,last_synced_at';
        $orderby = 'id desc';
        $res = SiteOrdersService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }

    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,site_id,order_id,order_no,user_id,order_type,good_id,good_info,amount,pay_channel_id,status,extra,created_at,updated_at,paid_at,last_synced_at';
        $info = SiteOrdersService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '订单不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }

    public function update()
    {
        $postField = 'id,order_type,amount,pay_channel_id,status,extra,paid_at,good_id,good_info';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        if ($id <= 0) {
            return $this->ajaxReturn(400, '参数错误');
        }
        $ok = SiteOrdersService::update($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }
}
