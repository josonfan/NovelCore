<?php
namespace app\admin\controller;

use app\admin\service\NovelsService;

class Novels extends Backend
{
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $categoryId = $this->request->param('category_id', 0, 'intval');
        $status = $this->request->param('status', null, 'intval');
        $isVip = $this->request->param('is_vip', null, 'intval');
        $isR18 = $this->request->param('is_r18', null, 'intval');
        $title = $this->request->param('title', null, 'trim');
        if (!empty($title)) $where['title|intro'] = ['like', "%{$title}%"];
        if ($categoryId > 0) $where['category_id'] = $categoryId;
        if ($status !== null) $where['status'] = $status;
        if ($isVip !== null) $where['is_vip'] = $isVip;
        if ($isR18 !== null) $where['is_r18'] = $isR18;
        $tagId = $this->request->param('tag_id', null, 'intval');
        if ($tagId !== null && $tagId > 0) {
            $ids = \app\admin\model\NovelTags::where('tag_id', (int)$tagId)->column('novel_id');
            $ids = array_values(array_unique(array_map('intval', (array)$ids)));
            if (empty($ids)) {
                return $this->ajaxReturn(200, '成功', ['list' => [], 'count' => 0]);
            }
            $where['id'] = ['in', $ids];
        }
        $field = 'id,novel_uuid,title,slug,author_id,author_name,category_id,cover,intro,status,is_r18,is_vip,word_count,like_count,fav_count,view_count,audit_status,audit_remark,audit_admin_id,audit_at,created_at,updated_at';
        $orderby = 'id desc';
        $res = NovelsService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,novel_uuid,title,slug,author_id,author_name,category_id,cover,intro,status,is_r18,is_vip,word_count,like_count,fav_count,view_count,audit_status,audit_remark,audit_admin_id,audit_at,created_at,updated_at';
        $info = NovelsService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '小说不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }
    public function create()
    {
        $postField = 'novel_uuid,title,author_id,author_name,category_id,cover,intro,status,is_r18,is_vip,seo_title,seo_keywords,seo_description';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $m = NovelsService::create($data);
        $out = getArrayByFields($m->toArray(), 'id,novel_uuid,title,slug,author_id,author_name,category_id,cover,intro,status,is_r18,is_vip,seo_title,seo_keywords,seo_description,created_at,updated_at');
        return $this->ajaxReturn(200, '创建成功', $out);
    }
    public function update()
    {
        $postField = 'id,title,slug,author_id,author_name,category_id,cover,intro,status,is_r18,is_vip,seo_title,seo_keywords,seo_description';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        $ok = NovelsService::update($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }
    public function delete()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $ok = NovelsService::delete($id);
        return $this->ajaxReturn(200, '删除成功', ['id' => $id, 'success' => $ok]);
    }
    public function bindTags()
    {
        $postField = 'novel_id,tag_ids';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $novelId = (int)($data['novel_id'] ?? 0);
        $tagIds = array_filter(array_map('intval', explode(',', (string)($data['tag_ids'] ?? ''))));
        $ok = NovelsService::bindTags($novelId, $tagIds);
        return $this->ajaxReturn(200, '绑定成功', ['success' => $ok]);
    }
    public function audit()
    {
        $postField = 'id,audit_status,audit_remark';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        $status = (int)($data['audit_status'] ?? 0);
        $remark = (string)($data['audit_remark'] ?? '');
        $adminId = (int)($this->request->uid ?? 0);
        $ok = NovelsService::audit($id, $status, $remark, $adminId);
        return $this->ajaxReturn(200, '审核已更新', ['id' => $id, 'success' => $ok]);
    }
}
