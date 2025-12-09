<?php
namespace app\admin\controller;

use app\admin\service\ChaptersService;

class Chapters extends Backend
{
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $novelId = $this->request->param('novel_id', 0, 'intval');
        if ($novelId > 0) $where['novel_id'] = $novelId;
        $field = 'id,chapter_uuid,novel_id,title,is_free,is_vip,price,word_count,sort_order,audit_status,audit_remark,audit_admin_id,audit_at,created_at,updated_at';
        $orderby = 'sort_order asc, id asc';
        $res = ChaptersService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,chapter_uuid,novel_id,title,is_free,is_vip,price,word_count,sort_order,audit_status,audit_remark,audit_admin_id,audit_at,created_at,updated_at';
        $info = ChaptersService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '章节不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }
    public function content()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $content = ChaptersService::content($id);
        if ($content === null) {
            return $this->ajaxReturn(404, '章节正文不存在');
        }
        return $this->ajaxReturn(200, '成功', ['id' => $id, 'content' => $content]);
    }
    public function create()
    {
        $postField = 'chapter_uuid,novel_id,title,content_short,is_free,is_vip,price,word_count,sort_order,content';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $m = ChaptersService::create($data);
        $out = getArrayByFields($m->toArray(), 'id,chapter_uuid,novel_id,title,content_short,is_free,is_vip,price,word_count,sort_order,audit_status,audit_remark,audit_admin_id,audit_at,created_at,updated_at');
        return $this->ajaxReturn(200, '创建成功', $out);
    }
    public function update()
    {
        $postField = 'id,title,content_short,is_free,is_vip,price,word_count,sort_order,audit_status,audit_remark,audit_admin_id,audit_at,content';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        $ok = ChaptersService::update($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }
    public function delete()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $ok = ChaptersService::delete($id);
        return $this->ajaxReturn(200, '删除成功', ['id' => $id, 'success' => $ok]);
    }
}
