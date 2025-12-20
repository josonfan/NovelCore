<?php
namespace app\admin\controller;

use app\admin\service\SiteCommentsService;

class SiteComments extends Backend
{
    /**
     * 站点评论列表
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param int $site_id 站点ID
     * @param int $novel_id 小说ID
     * @param int $user_id 用户ID
     * @param int $status 状态
     * @param string $content 内容(模糊搜索)
     */
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        
        $siteId = $this->request->param('site_id');
        if ($siteId !== null && $siteId !== '') {
            $where[] = ['site_id', '=', (int)$siteId];
        }

        $novelId = $this->request->param('novel_id');
        if ($novelId !== null && $novelId !== '') {
            $where[] = ['novel_id', '=', (int)$novelId];
        }

        $userId = $this->request->param('user_id');
        if ($userId !== null && $userId !== '') {
            $where[] = ['user_id', '=', (int)$userId];
        }
        
        $status = $this->request->param('status');
        if ($status !== null && $status !== '') {
            $where[] = ['status', '=', (int)$status];
        }

        $content = $this->request->param('content');
        if ($content) {
            $where[] = ['content', 'like', "%{$content}%"];
        }

        $field = '*';
        $orderby = 'id desc';
        
        $res = SiteCommentsService::list($where, $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }

    /**
     * 站点评论详情
     * @param int $id ID
     */
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = '*';
        $info = SiteCommentsService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '评论不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }

    /**
     * 审核评论
     * @param int $id ID
     * @param int $status 状态(0=待审核, 1=通过, 2=拒绝)
     * @param string $audit_reason 审核原因
     */
    public function audit()
    {
        $id = (int)$this->request->param('id');
        $status = (int)$this->request->param('status');
        $auditReason = (string)$this->request->param('audit_reason', '');
        $ok = SiteCommentsService::audit($id, $status, $auditReason);
        return $this->ajaxReturn(200, '操作成功', ['success' => $ok]);
    }
}
