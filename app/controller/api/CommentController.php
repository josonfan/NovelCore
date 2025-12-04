<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\exception\BusinessException;
use app\model\Comment;
use app\model\CommentLike;
use app\model\Novel;
use GuzzleHttp\Client;
use app\service\UserStatsService;
use think\Request;

/**
 * 评论接口，外部 ID 使用 novel_uuid，内部查询使用自增 ID。
 */
class CommentController extends BaseController
{
    /**
     * 获取评论列表，仅返回审核通过的评论（status=1）。
     */
    public function index(string $novelId, Request $request)
    {
        $novel = $this->findNovelByUuid($novelId);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $page     = max(1, (int) $request->get('page', 1));
        $pageSize = min(50, max(1, (int) $request->get('page_size', 10)));
        $order    = strtolower((string) $request->get('order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = Comment::with(['user'])
            ->where('novel_id', $novel->id)
            ->where('status', 1)
            ->order('id', $order);

        $paginator = $query->paginate([
            'list_rows' => $pageSize,
            'page'      => $page,
        ]);

        $comments = [];
        foreach ($paginator->items() as $item) {
            $comments[] = $this->formatComment($item);
        }

        return json_success([
            'total'     => $paginator->total(),
            'page'      => $paginator->currentPage(),
            'page_size' => $paginator->listRows(),
            'comments'  => $comments,
        ], '获取成功');
    }

    /**
     * 发表评论（楼中楼通过 parent_id/root_id 处理），默认待审核。
     */
    public function store(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = $this->findNovelByUuid($novelId);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $content = trim((string) $request->post('content', ''));
        $parentId = (int) $request->post('parent_id', 0);
        $isR18 = 0; // 预留内容审核逻辑

        if ($content === '') {
            return json_error('评论内容不能为空');
        }

        $parent = null;
        $rootId = null;
        if ($parentId > 0) {
            $parent = Comment::where('novel_id', $novel->id)->find($parentId);
            if (!$parent) {
                return json_error('父评论不存在', 404)->code(404);
            }
            $rootId = $parent->root_id ?: $parent->id;
        }

        $comment = new Comment();
        $comment->save([
            'novel_id'      => $novel->id,
            'chapter_id'    => null,
            'user_id'       => $user->id,
            'parent_id'     => $parentId ?: null,
            'root_id'       => $rootId,
            'content'       => $content,
            'is_r18'        => $isR18,
            'status'        => 0, // 待审核
            'review_source' => 0, // 未审核
        ]);

        // 统计：评论数 +1
        UserStatsService::incCommentCount($user->id, 1);

        // 上报审核（同步占位，可改为异步）
        $this->reportForModeration($comment);

        return json_success($this->formatComment($comment), '评论已提交，审核中');
    }

    /**
     * 点赞评论。
     */
    public function like(int $commentId, Request $request)
    {
        $user = $request->user;
        $comment = Comment::find($commentId);
        if (!$comment) {
            return json_error('评论不存在', 404)->code(404);
        }

        $exists = CommentLike::where('user_id', $user->id)
            ->where('comment_id', $comment->id)
            ->find();

        if (!$exists) {
            CommentLike::create([
                'user_id'    => $user->id,
                'comment_id' => $comment->id,
            ]);
            Comment::where('id', $comment->id)->inc('like_count')->update();
        }

        return json_success([
            'comment_id' => $comment->id,
            'liked'      => true,
        ], '点赞成功');
    }

    /**
     * 取消点赞评论。
     */
    public function unlike(int $commentId, Request $request)
    {
        $user = $request->user;
        $comment = Comment::find($commentId);
        if (!$comment) {
            return json_error('评论不存在', 404)->code(404);
        }

        $deleted = CommentLike::where('user_id', $user->id)
            ->where('comment_id', $comment->id)
            ->delete();

        if ($deleted) {
            Comment::where('id', $comment->id)
                ->where('like_count', '>', 0)
                ->dec('like_count')
                ->update();
        }

        return json_success([
            'comment_id' => $comment->id,
            'liked'      => false,
        ], '已取消点赞');
    }

    /**
     * 格式化评论返回体。
     */
    protected function formatComment(Comment $comment): array
    {
        $user = $comment->user;
        return [
            'id'         => $comment->id,
            'novel_id'   => $comment->novel_id,
            'chapter_id' => $comment->chapter_id,
            'parent_id'  => $comment->parent_id,
            'root_id'    => $comment->root_id,
            'content'    => $comment->content,
            'is_r18'     => (int) $comment->is_r18,
            'like_count' => (int) $comment->like_count,
            'status'     => (int) $comment->status,
            'review_source' => (int) $comment->review_source,
            'created_at' => $comment->created_at,
            'user'       => $user ? [
                'uid'      => $user->id,
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
            ] : null,
        ];
    }

    /**
     * 按 uuid（兼容自增 id）查小说。
     */
    protected function findNovelByUuid(string $novelId): ?Novel
    {
        $novel = Novel::where('novel_uuid', $novelId)->find();
        if (!$novel && ctype_digit($novelId)) {
            $novel = Novel::find((int) $novelId);
        }
        return $novel;
    }

    /**
     * 将评论上报到审核服务，失败忽略。
     */
    protected function reportForModeration(Comment $comment): void
    {
        $endpoint = (string) config('moderation.endpoint', '');
        if ($endpoint === '') {
            return;
        }
        $token = (string) config('moderation.token', '');

        $client = new Client(['timeout' => 3]);
        $payload = [
            'type'       => 'comment',
            'id'         => $comment->id,
            'content'    => $comment->content,
            'novel_id'   => $comment->novel_id,
            'chapter_id' => $comment->chapter_id,
            'user_id'    => $comment->user_id,
            'is_r18'     => $comment->is_r18,
        ];

        try {
            $client->post(rtrim($endpoint, '/'), [
                'headers' => [
                    'Authorization' => $token ? 'Bearer ' . $token : '',
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);
        } catch (\Throwable $e) {
            // 审核上报失败不影响用户流程
        }
    }
}
