<?php
declare(strict_types=1);

namespace app\service;

use app\model\User as UserModel;
use app\model\Comment;
use app\model\CommentLike;

class CommentViewService
{
    public static function formatRow(array $row, bool $isLiked = false): array
    {
        $userView = null;
        if (!empty($row['user_id'])) {
            $user = (new UserService())->info((int)$row['user_id'], 'id,nickname,avatar');
            if (!empty($user)) {
                $userView = [
                    'uid' => $user['id'] ?? null,
                    'nickname' => $user['nickname'] ?? '',
                    'avatar' => $user['avatar'] ?? '',
                ];
            }
        }
        return [
            'id' => $row['id'] ?? null,
            'novel_id' => $row['novel_id'] ?? null,
            'chapter_id' => $row['chapter_id'] ?? null,
            'parent_id' => $row['parent_id'] ?? null,
            'root_id' => $row['root_id'] ?? null,
            'content' => $row['content'] ?? '',
            'is_r18' => (int)($row['is_r18'] ?? 0),
            'like_count' => (int)($row['like_count'] ?? 0),
            'status' => (int)($row['status'] ?? 0),
            'review_source' => (int)($row['review_source'] ?? 0),
            'created_at' => $row['created_at'] ?? null,
            'user' => $userView,
            'is_liked' => (int)$isLiked,
        ];
    }

    public static function formatList(array $rows, int $currentUserId = 0): array
    {
        $likedIds = self::getLikedIds($rows, $currentUserId);
        $list = [];
        foreach ($rows as $row) {
            $isLiked = in_array($row['id'] ?? 0, $likedIds);
            $list[] = self::formatRow($row, $isLiked);
        }
        return $list;
    }

    public static function formatTree(array $rows, int $currentUserId = 0): array
    {
        $likedIds = self::getLikedIds($rows, $currentUserId);
        $likedIdSet = array_fill_keys(array_map('intval', $likedIds), true);
        $nodes = [];
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            $isLiked = isset($likedIdSet[$id]);
            $node = self::formatRow($row, $isLiked);
            $node['children'] = [];
            $nodes[$id] = $node;
        }
        $tree = [];
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            $pid = (int)($row['parent_id'] ?? 0);
            if ($id <= 0 || !isset($nodes[$id])) {
                continue;
            }
            if ($pid > 0 && isset($nodes[$pid])) {
                $nodes[$pid]['children'][] =& $nodes[$id];
            } else {
                $tree[] =& $nodes[$id];
            }
        }
        return $tree;
    }

    private static function getLikedIds(array $rows, int $userId): array
    {
        if ($userId <= 0 || empty($rows)) {
            return [];
        }
        $ids = array_column($rows, 'id');
        if (empty($ids)) {
            return [];
        }
        return CommentLike::where('user_id', $userId)
            ->whereIn('comment_id', $ids)
            ->column('comment_id');
    }

    public static function fetchThread(int $novelPk, int $rootId, int $currentUserId = 0): array
    {
        $rows = Comment::where('novel_id', $novelPk)
            ->where('root_id', $rootId)
            ->where('status', 1)
            ->order('id', 'asc')
            ->select()
            ->toArray();
        return self::formatTree($rows, $currentUserId);
    }

    public static function formatDetail(int $id, int $currentUserId = 0): array
    {
        $row = (new Comment())->infoById($id, 'id,novel_id,chapter_id,parent_id,root_id,content,is_r18,like_count,status,review_source,created_at,user_id');
        $isLiked = false;
        if ($currentUserId > 0) {
            $isLiked = (bool)CommentLike::where('user_id', $currentUserId)->where('comment_id', $id)->value('id');
        }
        return self::formatRow($row, $isLiked);
    }
}
