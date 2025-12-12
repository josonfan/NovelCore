<?php
declare(strict_types=1);

namespace app\service;

use app\model\UserSearchLog;

class SearchLogService
{
    public static function write(?int $userId, string $keyword, array $filters): void
    {
        try {
            (new UserSearchLog())->writeById(0, [
                'user_id' => $userId,
                'keyword' => $keyword,
                'filters_json' => $filters,
            ]);
        } catch (\Throwable $e) {
        }
    }
}
