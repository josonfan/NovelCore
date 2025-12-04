<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Comment;
use think\Request;

/**
 * 后台评论审核结果回调，受 AdminAuth 保护。
 */
class AdminCommentController extends BaseController
{
    /**
     * 更新评论审核状态。
     */
    public function updateStatus(int $id, Request $request)
    {
        $status = (int) $request->put('status', 0);
        $reviewSource = (int) $request->put('review_source', 0);

        $comment = Comment::find($id);
        if (!$comment) {
            return json_error('评论不存在', 404)->code(404);
        }

        if (!in_array($status, [1, 2], true)) {
            return json_error('无效的审核状态', 400)->code(400);
        }

        $comment->status = $status;
        $comment->review_source = $reviewSource;
        $comment->save();

        return json_success(['message' => 'Comment review status updated']);
    }
}
