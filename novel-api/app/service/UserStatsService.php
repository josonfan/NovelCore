<?php
declare(strict_types=1);

namespace app\service;

use app\model\UserProfileStats;

/**
 * 用户统计增量服务：将行为计数增量写入 Redis，后续定时任务合并入 user_profile_stats。
 */
class UserStatsService
{
    protected static function incField(int $userId, string $field, int $delta): void
    {
        if ($delta === 0) {
            return;
        }
        try {
            $model = new UserProfileStats();
            $current = $model->infoById($userId, $field);
            $exists = !empty($current);
            $newVal = (int)($current[$field] ?? 0) + (int)$delta;
            if ($exists) {
                $model->writeById($userId, [$field => $newVal]);
            } else {
                $model->writeById(0, [
                    'user_id' => $userId,
                    $field    => $newVal,
                ]);
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * 收藏数增量
     */
    public static function incFavoriteCount(int $userId, int $delta = 1): void
    {
        self::incField($userId, 'favorite_novel_count', $delta);
    }

    /**
     * 阅读过的小说数增量
     */
    public static function incReadNovelCount(int $userId, int $delta = 1): void
    {
        self::incField($userId, 'read_novel_count', $delta);
    }

    /**
     * 评论数增量
     */
    public static function incCommentCount(int $userId, int $delta = 1): void
    {
        self::incField($userId, 'comment_count', $delta);
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
        self::incField($userId, 'total_read_minutes', $minutes);
    }
}
