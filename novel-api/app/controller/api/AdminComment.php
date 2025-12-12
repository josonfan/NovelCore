<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Comment;
use think\Request;

class AdminComment extends BaseController
{
    /**
     * 更新评论审核状态（后台）
     *
     * 路由：`PUT /api/admin/comments/:id`
     * 鉴权：需 Admin Token
     * 参数：`status`（1通过、2拒绝），`review_source?`
     * 返回：操作结果
     *
     * @param int $id
     * @param Request $request
     * @return \think\Response
     */
    public function updateStatus(int $id, Request $request)
    {
        $status = (int) $request->put('status', 0);
        $reviewSource = (int) $request->put('review_source', 0);
        $comment = Comment::find($id);
        if (!$comment) {
            return api_response(404, '评论不存在', [])->code(404);
        }
        if (!in_array($status, [1, 2], true)) {
            return api_response(400, '无效的审核状态', [])->code(400);
        }
        (new Comment())->writeById((int)$comment->id, [
            'status' => $status,
            'review_source' => $reviewSource,
        ]);
        return api_response(200, '成功', ['message' => 'Comment review status updated']);
    }
}
