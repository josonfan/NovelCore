<?php
namespace app\admin\controller;

use app\admin\service\CommentReviewConfigService;

class CommentReviewConfig extends Backend
{
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $siteId = $this->request->param('site_id', null);
        if ($siteId !== null && $siteId !== '') {
            $where['site_id'] = (int)$siteId;
        }
        $field = 'id,site_id,enabled,require_approval,max_length,max_per_minute,forbidden_words_json,updated_at';
        $orderby = 'id desc';
        $res = CommentReviewConfigService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,site_id,enabled,require_approval,max_length,max_per_minute,forbidden_words_json,updated_at';
        $info = CommentReviewConfigService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }
    public function detailBySite()
    {
        $siteId = (int)$this->request->param('site_id', 0);
        $info = CommentReviewConfigService::getBySiteId($siteId);
        if (empty($info)) {
            return $this->ajaxReturn(404, '不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }

    public function save()
    {
        $postField = 'site_id,enabled,require_approval,max_length,max_per_minute,forbidden_words_json';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = CommentReviewConfigService::save($data);
        return $this->ajaxReturn(200, '保存成功', $res);
    }
}
