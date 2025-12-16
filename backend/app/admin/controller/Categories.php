<?php
namespace app\admin\controller;

use app\admin\service\CategoriesService;

class Categories extends Backend
{
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $field = 'id,name,slug,sort_order,is_active,seo_title,seo_keywords,seo_description,created_at,updated_at';
        $orderby = 'sort_order desc, id desc';
        $res = CategoriesService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,name,slug,sort_order,is_active,seo_title,seo_keywords,seo_description,created_at,updated_at';
        $info = CategoriesService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '分类不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }
    public function create()
    {
        $postField = 'name,sort_order,is_active,seo_title,seo_keywords,seo_description';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $m = CategoriesService::create($data);
        $out = getArrayByFields($m->toArray(), 'id,name,slug,sort_order,is_active,seo_title,seo_keywords,seo_description,created_at,updated_at');
        return $this->ajaxReturn(200, '创建成功', $out);
    }
    public function update()
    {
        $postField = 'id,name,sort_order,is_active,seo_title,seo_keywords,seo_description';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        $ok = CategoriesService::update($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }
    public function delete()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $ok = CategoriesService::delete($id);
        return $this->ajaxReturn(200, '删除成功', ['id' => $id, 'success' => $ok]);
    }
    public function toggle()
    {
        $postField = 'id,is_active';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        $isActive = (int)($data['is_active'] ?? 1);
        $ok = CategoriesService::toggle($id, $isActive);
        return $this->ajaxReturn(200, '启停成功', ['id' => $id, 'success' => $ok]);
    }
    public function options()
    {
        $onlyActive = (int)$this->request->param('active', 1, 'intval') === 1;
        $res = \app\admin\service\CategoriesService::options($onlyActive);
        return $this->ajaxReturn(200, '成功', $res);
    }   
}
