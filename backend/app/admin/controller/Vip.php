<?php
namespace app\admin\controller;

use app\admin\service\VipService;

class Vip extends Backend
{
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $name = $this->request->param('name');
        if (!empty($name)) {
            $where[] = ['name', 'like', "%{$name}%"];
        }
        $status = $this->request->param('status');
        if ($status !== null && $status !== '') {
            $where[] = ['status', '=', (int)$status];
        }
        $isHot = $this->request->param('is_hot');
        if ($isHot !== null && $isHot !== '') {
            $where[] = ['is_hot', '=', (int)$isHot];
        }
        $field = 'id,name,descript,days,price,old_price,sort,sold_num,status,is_hot,sold_total,return_total,return_num,created_at,updated_at';
        $orderby = 'sort desc, id desc';
        $res = VipService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }

    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,name,descript,days,price,old_price,sort,sold_num,status,is_hot,sold_total,return_total,return_num,created_at,updated_at';
        $info = VipService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '套餐不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }

    public function create()
    {
        $postField = 'name,descript,days,price,old_price,sort,status,is_hot';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $m = VipService::create($data);
        $out = getArrayByFields($m->toArray(), 'id,name,descript,days,price,old_price,sort,sold_num,status,is_hot,sold_total,return_total,return_num,created_at,updated_at');
        return $this->ajaxReturn(200, '创建成功', $out);
    }

    public function update()
    {
        $postField = 'id,name,descript,days,price,old_price,sort,status,is_hot';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        $ok = VipService::update($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }

    public function delete()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $ok = VipService::delete($id);
        return $this->ajaxReturn(200, '删除成功', ['id' => $id, 'success' => $ok]);
    }

    public function toggle()
    {
        $postField = 'id,status';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        $status = (int)($data['status'] ?? 1);
        $ok = VipService::setStatus($id, $status);
        return $this->ajaxReturn(200, '设置成功', ['id' => $id, 'success' => $ok]);
    }
}
