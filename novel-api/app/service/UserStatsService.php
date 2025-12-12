<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Cache;

/**
 * 用户统计增量服务：将行为计数增量写入 Redis，后续定时任务合并入 user_profile_stats。
 */
class UserStatsService
{
    protected static function key(int $userId): string
    {
        return 'user:stats:delta:' . $userId;
    }

    /**
     * 收藏数增量
     */
    public static function incFavoriteCount(int $userId, int $delta = 1): void
    {
        Cache::store('redis')->handler()->hIncrBy(self::key($userId), 'favorite_novel_count', $delta);
    }

    /**
     * 阅读过的小说数增量
     */
    public static function incReadNovelCount(int $userId, int $delta = 1): void
    {
        Cache::store('redis')->handler()->hIncrBy(self::key($userId), 'read_novel_count', $delta);
    }

    /**
     * 评论数增量
     */
    public static function incCommentCount(int $userId, int $delta = 1): void
    {
        Cache::store('redis')->handler()->hIncrBy(self::key($userId), 'comment_count', $delta);
    }

    /**
     * 阅读分钟数增量（传入秒数，换算为分钟向下取整）
     */
    public static function incReadMinutes(int $userId, int $seconds): void
    {
        $minutes = (int) floor($seconds / 60);
        if ($minutes <= 0) {
            return;
        }
        Cache::store('redis')->handler()->hIncrBy(self::key($userId), 'total_read_minutes', $minutes);
    }
}
