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
            $where[] = ['site_id', '=', (int)$siteId];
        }
        $userId = $this->request->param('user_id');
        if ($userId !== null && $userId !== '') {
            $where[] = ['user_id', '=', (int)$userId];
        }
        $orderNo = $this->request->param('order_no');
        if (!empty($orderNo)) {
            $where[] = ['order_no', 'like', "%{$orderNo}%"];
        }
        $status = $this->request->param('status');
        if ($status !== null && $status !== '') {
            $where[] = ['status', '=', (string)$status];
        }
        $orderType = $this->request->param('order_type');
        if ($orderType !== null && $orderType !== '') {
            $where[] = ['order_type', '=', (string)$orderType];
        }
        $payChannel = $this->request->param('pay_channel');
        if ($payChannel !== null && $payChannel !== '') {
            $where[] = ['pay_channel', '=', (string)$payChannel];
        }
        $field = 'id,site_id,order_no,user_id,order_type,novel_id,chapter_id,vip_plan,amount,pay_channel,status,extra,site_created_at,site_paid_at,last_synced_at';
        $orderby = 'id desc';
        $res = SiteOrdersService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }

    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,site_id,order_no,user_id,order_type,novel_id,chapter_id,vip_plan,amount,pay_channel,status,extra,site_created_at,site_paid_at,last_synced_at';
        $info = SiteOrdersService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '订单不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }

    public function update()
    {
        $postField = 'id,order_type,vip_plan,amount,pay_channel,status,extra,site_paid_at';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        $ok = SiteOrdersService::update($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }
}

