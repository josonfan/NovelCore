<?php
declare(strict_types=1);

namespace app\service;

use app\model\UserSearchLog;

class SearchLogService
{
    public static function write(string $keyword, array $filters): void
    {
        try {
            $m = new UserSearchLog();
            $id = $m->where('keyword', $keyword)->value('id');
            if ($id) {
                $current =$m->infoById((int)$id)['count'] ?? 0;
                $m->writeById((int)$id, [
                    'count' => $current + 1,
                    'keyword' => $keyword,
                ]);
            } else {
                $m->writeById(0, [
                    'count' => 1,
                    'keyword' => $keyword,
                ]);
            }
        } catch (\Throwable $e) {
            
        }
    }
    public static function getHotKeywords(int $limit = 10): array
    {
        try {
            $m = new UserSearchLog();
            $list = $m->order('count', 'desc')->field('keyword, count')->limit($limit)->select()->toArray();
            return $list;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
