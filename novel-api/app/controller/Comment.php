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
        $view     = $this->request->param('view', 'flat', 'trim');
        $rootId   = $this->request->param('rootId', 0, 'intval');
        $res = CommentService::list((int)$novel['id'], $page, $pageSize, $order);
        $list = $view === 'tree'
            ? \app\service\CommentViewService::formatTree($res['list'] ?? [])
            : \app\service\CommentViewService::formatList($res['list'] ?? []);
        if ($view === 'thread' && $rootId > 0) {
            $list = \app\service\CommentViewService::fetchThread((int)$novel['id'], $rootId);
        }
        return $this->ajaxReturn(200, '获取成功', [
            'list'  => $list,
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
        $novel = \app\service\NovelService::getInfoByUuid($novelId, 'id,novel_uuid');
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
            'novel_id'   => (string)$novel['novel_uuid'],
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
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $data = \app\service\CommentLikeService::like($userId, $commentId);
        return $this->ajaxReturn(200, '点赞成功', $data);
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
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $data = \app\service\CommentLikeService::unlike($userId, $commentId);
        return $this->ajaxReturn(200, '已取消点赞', $data);
    }
}
