<?php
declare(strict_types=1);

namespace app\service;

use app\model\UserProfileStats;

class UserSummaryService
{
    /**
     * 我的页统计汇总
     *
     * - user：基础用户信息视图
     * - stats：行为统计（收藏、阅读、评论、阅读时长）
     */
    public static function summary(int $userId): array
    {
        // UserService::ensureUserExists($userId);
        $user = UserService::info($userId, 'id,username,nickname,avatar,vip_expire,created_at');
        $statsModel = new UserProfileStats();
        $stored = $statsModel->infoById($userId, 'favorite_novel_count,read_novel_count,comment_count,total_read_minutes');
        $base = [
            'favorite_novel_count' => (int)($stored['favorite_novel_count'] ?? 0),
            'read_novel_count'     => (int)($stored['read_novel_count'] ?? 0),
            'comment_count'        => (int)($stored['comment_count'] ?? 0),
            'total_read_minutes'   => (int)($stored['total_read_minutes'] ?? 0),
        ];
        return [
            'user'  => [
                'id'         => (int)($user['id'] ?? 0),
                'username'   => (string)($user['username'] ?? ''),
                'nickname'   => (string)($user['nickname'] ?? ''),
                'avatar'     => (string)($user['avatar'] ?? ''),
                'vip_expire' => (int)($user['vip_expire'] ?? 0),
                'created_at' => (string)($user['created_at'] ?? ''),
            ],
            'stats' => $base,
        ];
    }
}

