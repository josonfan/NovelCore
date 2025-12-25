<?php
declare(strict_types=1);

namespace app\service;

use app\model\Comment as CommentModel;
use app\model\CommentLike;
use think\exception\HttpException;

class CommentLikeService
{
    public static function like(int $userId, int $commentId): array
    {
        $cid = (int) CommentModel::where('id', $commentId)->value('id');
        if (!$cid) {
            throw new HttpException(404, '评论不存在');
        }
        $existsId = CommentLike::where('user_id', $userId)
            ->where('comment_id', $cid)
            ->value('id');
        if (!$existsId) {
            (new CommentLike())->writeById(0, [
                'user_id'    => $userId,
                'comment_id' => $cid,
            ]);
            $info = (new CommentModel())->infoById($cid, 'like_count');
            (new CommentModel())->writeById($cid, [
                'like_count' => (int)($info['like_count'] ?? 0) + 1,
            ]);
        }
        return [
            'comment_id' => $cid,
            'liked'      => true,
        ];
    }

    public static function unlike(int $userId, int $commentId): array
    {
        $cid = (int) CommentModel::where('id', $commentId)->value('id');
        if (!$cid) {
            throw new HttpException(404, '评论不存在');
        }
        $likeId = CommentLike::where('user_id', $userId)
            ->where('comment_id', $cid)
            ->value('id');
        if ($likeId) {
            (new CommentLike())->deleteById((int)$likeId);
            $info = (new CommentModel())->infoById($cid, 'like_count');
            $newCount = max(0, (int)($info['like_count'] ?? 0) - 1);
            (new CommentModel())->writeById($cid, [
                'like_count' => $newCount,
            ]);
        }
        return [
            'comment_id' => $cid,
            'liked'      => false,
        ];
    }
}

