<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\exception\BusinessException;
use app\model\Comment as CommentModel;
use app\model\CommentLike;
use app\model\Novel;
use app\service\ContentService;
use app\service\CommentService;
use app\service\UserStatsService;
use think\Request;

class Comment extends BaseController
{
    /**
     * 评论列表
     *
     * 路由：`GET|POST /api/novels/:novelId/comments`
     * 鉴权：无需登录（仅返回审核通过的评论）
     * 分页：`page`、`limit`（默认10，最大50）；排序 `order`（`asc|desc`）
     * 返回：列表与总数
     *
     * @param string $novelId
     * @param Request $request
     * @return \think\Response
     */
    public function index(string $novelId, Request $request)
    {
        $novel = ContentService::getNovelOrFail($novelId);
        $page     = max(1, (int) $request->get('page', 1));
        $pageSize = min(50, max(1, (int) $request->get('limit', 10)));
        $order    = (string) $request->get('order', 'desc');
        $res = CommentService::list((int)$novel->id, $page, $pageSize, $order);
        return api_response(200, '成功', $res['list'], (int)$res['count']);
    }

    /**
     * 发表评论
     *
     * 路由：`POST /api/novels/:novelId/comments`
     * 鉴权：需登录
     * 参数：`content` 文本内容；`parent_id?` 父评论ID
     * 返回：提交视图（默认待审核）
     *
     * @param string $novelId
     * @param Request $request
     * @return \think\Response
     */
    public function store(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = ContentService::getNovelOrFail($novelId);
        $content = (string) $request->post('content', '');
        $parentId = (int) $request->post('parent_id', 0);
        $isR18 = 0;
        $validated = CommentService::validateStore((int)$novel->id, (int)$user->id, $content, $parentId);
        $rootId = $validated['root_id'];
        (new CommentModel())->writeById(0, [
            'novel_id'      => $novel->id,
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
            'novel_id' => $novel->id,
            'chapter_id' => null,
            'user_id' => $user->id,
            'is_r18' => $isR18,
        ]);
        return api_response(200, '评论已提交，审核中', [
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
     *
     * 路由：`POST /api/comments/:commentId/like`
     * 鉴权：需登录
     * 参数：`:commentId` 评论内部ID
     * 返回：`{ comment_id, liked:true }`
     *
     * @param int $commentId
     * @param Request $request
     * @return \think\Response
     */
    public function like(int $commentId, Request $request)
    {
        $user = $request->user;
        $commentIdVal = CommentModel::where('id', $commentId)->value('id');
        if (!$commentIdVal) {
            return api_response(404, '评论不存在', [])->code(404);
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
        return api_response(200, '点赞成功', [
            'comment_id' => $commentIdVal,
            'liked'      => true,
        ]);
    }

    /**
     * 取消点赞评论
     *
     * 路由：`DELETE /api/comments/:commentId/like`
     * 鉴权：需登录
     * 参数：`:commentId` 评论内部ID
     * 返回：`{ comment_id, liked:false }`
     *
     * @param int $commentId
     * @param Request $request
     * @return \think\Response
     */
    public function unlike(int $commentId, Request $request)
    {
        $user = $request->user;
        $commentIdVal = CommentModel::where('id', $commentId)->value('id');
        if (!$commentIdVal) {
            return api_response(404, '评论不存在', [])->code(404);
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
        return api_response(200, '已取消点赞', [
            'comment_id' => $commentIdVal,
            'liked'      => false,
        ]);
    }

    
}
