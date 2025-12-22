<?php
namespace app\admin\controller;

use app\admin\service\PaymentChannelService;

class PaymentChannel extends Backend
{
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $field = 'id,name,lang,is_usdt,order_quantity,payment_quantity,place_order,payment,limit_price,pay_rules,cycle_price,status,remarks,pay_url,sup_order_url,pay_id,skey,md5_key,pay_bankcode,sort,is_web,not_pc,icon_iden,is_default,created_at,updated_at,mark';
        $orderby = 'sort desc, id desc';
        $res = PaymentChannelService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }

    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,name,lang,is_usdt,order_quantity,payment_quantity,place_order,payment,limit_price,pay_rules,cycle_price,status,remarks,pay_url,sup_order_url,pay_id,skey,md5_key,pay_bankcode,sort,is_web,not_pc,icon_iden,is_default,created_at,updated_at,mark';
        $info = PaymentChannelService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '渠道不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }

    public function create()
    {
        $postField = 'name,lang,is_usdt,limit_price,pay_rules,cycle_price,status,remarks,pay_url,sup_order_url,pay_id,skey,md5_key,pay_bankcode,sort,is_web,not_pc,icon_iden,is_default';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $m = PaymentChannelService::create($data);
        $out = getArrayByFields($m->toArray(), 'id,name,lang,is_usdt,order_quantity,payment_quantity,place_order,payment,limit_price,pay_rules,cycle_price,status,remarks,pay_url,sup_order_url,pay_id,skey,md5_key,pay_bankcode,sort,is_web,not_pc,icon_iden,is_default,created_at,updated_at,mark');
        return $this->ajaxReturn(200, '创建成功', $out);
    }

    public function update()
    {
        $postField = 'id,name,lang,is_usdt,limit_price,pay_rules,cycle_price,status,remarks,pay_url,sup_order_url,pay_id,skey,md5_key,pay_bankcode,sort,is_web,not_pc,icon_iden,is_default';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        $ok = PaymentChannelService::update($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }

    public function delete()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $ok = PaymentChannelService::delete($id);
        return $this->ajaxReturn(200, '删除成功', ['id' => $id, 'success' => $ok]);
    }

    public function toggle()
    {
        $id = (int)$this->request->param('id');
        $status = (int)$this->request->param('status');
        $ok = PaymentChannelService::toggle($id, $status);
        return $this->ajaxReturn(200, '设置成功', ['success' => $ok]);
    }
}
