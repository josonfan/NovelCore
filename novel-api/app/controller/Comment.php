<?php
declare(strict_types=1);

namespace app\controller;

use app\model\Comment as CommentModel;
use app\model\CommentLike;
use app\service\ContentService;
use app\service\CommentService;
use app\service\UserStatsService;

class Comment extends Common
{
    /**
     * 评论列表
     * 路由：POST /api/Comment/index
     * 鉴权：无需登录
     * 入参：novelId（外部 novel_uuid）, page, limit, order
     * 返回：data { list, count }
     */
    public function index()
    {
        $novelId = $this->request->param('novelId', '', 'trim');
        $novel = \app\service\NovelService::getInfoByUuid($novelId, 'id,novel_uuid');
        $page     = $this->request->param('page', 1, 'intval');
        $pageSize = $this->request->param('limit', 10, 'intval');
        $order    = $this->request->param('order', 'desc', 'trim');
        $res = CommentService::list((int)$novel->id, $page, $pageSize, $order);
        return $this->ajaxReturn(200, '成功', [
            'list'  => $res['list'],
            'count' => (int)$res['count'],
        ]);
    }

    /**
     * 发表评论
     * 路由：POST /api/Comment/store
     * 鉴权：需登录
     * 入参：novelId（外部 novel_uuid）, content, parent_id?
     * 返回：data 评论草稿信息（待审核）
     */
    public function store()
    {
        $novelId = $this->request->param('novelId', '', 'trim');
        $user = $this->request->user;
        $novel = ContentService::getNovelOrFail($novelId);
        $content = (string) $this->request->post('content', '');
        $parentId = (int) $this->request->post('parent_id', 0);
        $isR18 = 0;
        $validated = CommentService::validateStore((int)$novel['id'], (int)$user->id, $content, $parentId);
        $rootId = $validated['root_id'];
        (new CommentModel())->writeById(0, [
            'novel_id'      => (int)$novel['id'],
            'chapter_id'    => null,
            'user_id'       => $user->id,
            'parent_id'     => $parentId ?: null,
            'root_id'       => $rootId,
            'content'       => $validated['content'],
            'is_r18'        => $isR18,
            'status'        => 0,
            'review_source' => 0,
        ]);
        UserStatsService::incCommentCount($user->id, 1);
        CommentService::report([
            'id' => null,
            'content' => $validated['content'],
            'novel_id' => (int)$novel['id'],
            'chapter_id' => null,
            'user_id' => $user->id,
            'is_r18' => $isR18,
        ]);
        return $this->ajaxReturn(200, '评论已提交，审核中', [
            'id'         => null,
            'novel_id'   => $novel->id,
            'chapter_id' => null,
            'parent_id'  => $parentId ?: null,
            'root_id'    => $rootId,
            'content'    => $content,
            'is_r18'     => (int)$isR18,
            'like_count' => 0,
            'status'     => 0,
            'review_source' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'user'       => [
                'uid'      => $user->id,
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
            ],
        ]);
    }

    /**
     * 点赞评论
     * 路由：POST /api/Comment/like
     * 鉴权：需登录
     * 入参：commentId
     * 返回：data { comment_id, liked:true }
     */
    public function like()
    {
        $commentId = $this->request->param('commentId', 0, 'intval');
        $user = $this->request->user;
        $commentIdVal = CommentModel::where('id', $commentId)->value('id');
        if (!$commentIdVal) {
            return $this->ajaxReturn(404, '评论不存在', []);
        }
        $existsId = CommentLike::where('user_id', $user->id)
            ->where('comment_id', $commentIdVal)
            ->value('id');
        if (!$existsId) {
            (new CommentLike())->writeById(0, [
                'user_id'    => $user->id,
                'comment_id' => $commentIdVal,
            ]);
            $commentInfo = (new CommentModel())->infoById($commentIdVal, 'like_count');
            (new CommentModel())->writeById((int)$commentIdVal, [
                'like_count' => (int)($commentInfo['like_count'] ?? 0) + 1,
            ]);
        }
        return $this->ajaxReturn(200, '点赞成功', [
            'comment_id' => $commentIdVal,
            'liked'      => true,
        ]);
    }

    /**
     * 取消点赞评论
     * 路由：POST /api/Comment/unlike
     * 鉴权：需登录
     * 入参：commentId
     * 返回：data { comment_id, liked:false }
     */
    public function unlike()
    {
        $commentId = $this->request->param('commentId', 0, 'intval');
        $user = $this->request->user;
        $commentIdVal = CommentModel::where('id', $commentId)->value('id');
        if (!$commentIdVal) {
            return $this->ajaxReturn(404, '评论不存在', []);
        }
        $likeId = CommentLike::where('user_id', $user->id)
            ->where('comment_id', $commentIdVal)
            ->value('id');
        if ($likeId) {
            (new CommentLike())->deleteById((int)$likeId);
            $commentInfo = (new CommentModel())->infoById($commentIdVal, 'like_count');
            $newCount = max(0, (int)($commentInfo['like_count'] ?? 0) - 1);
            (new CommentModel())->writeById((int)$commentIdVal, [
                'like_count' => $newCount,
            ]);
        }
        return $this->ajaxReturn(200, '已取消点赞', [
            'comment_id' => $commentIdVal,
            'liked'      => false,
        ]);
    }
}
