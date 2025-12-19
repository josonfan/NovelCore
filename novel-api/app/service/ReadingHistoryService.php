<?php
declare(strict_types=1);

namespace app\service;

use app\model\Novel;
use app\model\Chapter;
use app\model\UserReadingHistory;

class ReadingHistoryService
{
    public static function getListByUser(int $userId, int $page = 1, int $limit = 10): array
    {
        $paginator = UserReadingHistory::where('user_id', $userId)
            ->order('last_read_at', 'desc')
            ->paginate(['list_rows' => $limit, 'page' => $page]);
        $storage = new \app\service\StorageService();
        $novelFields = 'novel_uuid as novel_id,title as novel_title,cover,status,is_vip,word_count,updated_at';
        $chapterFields = 'chapter_uuid as chapter_id,title as chapter_title';
        $novelModel = new Novel();
        $chapterModel = new Chapter();
        $items = [];
        foreach ($paginator->items() as $h) {
            $novel = $novelModel->infoById((int)$h['novel_id'], $novelFields);
            $novel['cover'] = $storage->getPublicUrl((string) ($novel['cover'] ?? ''));
            $chapter = $chapterModel->infoById((int)$h['chapter_id'], $chapterFields);
            $items[] = [
                'novel_id'     => $novel['novel_id'] ?? null,
                'novel_title'  => $novel['novel_title'] ?? null,
                'cover'        => $novel['cover'] ?? null,
                'status'       => (int) ($novel['status'] ?? 0),
                'is_vip'       => (int) ($novel['is_vip'] ?? 0),
                'word_count'   => (int) ($novel['word_count'] ?? 0),
                'updated_at'   => $novel['updated_at'] ?? null,
                'chapter_id'   => $chapter['chapter_id'] ?? null,
                'chapter_title'=> $chapter['chapter_title'] ?? null,
                'progress'     => (float) $h['progress'],
                'last_read_at' => $h['last_read_at'],
            ];
        }
        return ['list' => $items, 'total' => (int) $paginator->total()];
    }
}

