<?php
namespace app\admin\controller;

use app\admin\service\SitesService;

class Sites extends Backend
{
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $field = 'id,name,code,base_api_url,primary_domain,is_active,remark,created_at,updated_at';
        $orderby = 'id desc';
        $res = SitesService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,name,code,base_api_url,primary_domain,is_active,remark,created_at,updated_at';
        $info = SitesService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '站点不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }
    public function create()
    {
        $postField = 'name,code,base_api_url,primary_domain,api_token,is_active,remark';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $m = SitesService::create($data);
        $out = getArrayByFields($m->toArray(), 'id,name,code,base_api_url,primary_domain,is_active,remark,created_at,updated_at');
        return $this->ajaxReturn(200, '创建成功', $out);
    }
    public function update()
    {
        $postField = 'id,name,code,base_api_url,primary_domain,is_active,remark';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        $ok = SitesService::update($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }
    public function delete()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $ok = SitesService::delete($id);
        return $this->ajaxReturn(200, '删除成功', ['id' => $id, 'success' => $ok]);
    }
    public function toggle()
    {
        $postField = 'id,is_active';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        $isActive = (int)($data['is_active'] ?? 1);
        $ok = SitesService::toggle($id, $isActive);
        return $this->ajaxReturn(200, '启停成功', ['id' => $id, 'success' => $ok]);
    }
}

