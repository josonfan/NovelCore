<?php
declare(strict_types=1);

namespace app\service;

use app\model\User as UserModel;
use app\model\Comment;

class CommentViewService
{
    public static function formatRow(array $row): array
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
        ];
    }

    public static function formatList(array $rows): array
    {
        $list = [];
        foreach ($rows as $row) {
            $list[] = self::formatRow($row);
        }
        return $list;
    }

    public static function formatTree(array $rows): array
    {
        $nodes = [];
        foreach ($rows as $row) {
            $node = self::formatRow($row);
            $node['children'] = [];
            $nodes[(int)($row['id'] ?? 0)] = $node;
        }
        $tree = [];
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            $pid = (int)($row['parent_id'] ?? 0);
            if ($pid > 0 && isset($nodes[$pid])) {
                $nodes[$pid]['children'][] = $nodes[$id];
            } else {
                $tree[] = $nodes[$id];
            }
        }
        return $tree;
    }

    public static function fetchThread(int $novelPk, int $rootId): array
    {
        $rows = Comment::where('novel_id', $novelPk)
            ->where('root_id', $rootId)
            ->where('status', 1)
            ->order('id', 'asc')
            ->select()
            ->toArray();
        return self::formatTree($rows);
    }

    public static function formatDetail(int $id): array
    {
        $row = (new Comment())->infoById($id, 'id,novel_id,chapter_id,parent_id,root_id,content,is_r18,like_count,status,review_source,created_at,user_id');
        return self::formatRow($row);
    }
}
