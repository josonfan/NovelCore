<?php
declare(strict_types=1);

namespace app\service;

use app\model\UserReadLog;
use app\model\UserReadingHistory;
use think\exception\HttpException;



class ReadingService
{
    public static function getProgress(int $userId, string $novelUuid): array
    {
        $novel = NovelService::getInfoByUuid($novelUuid, 'id,novel_uuid');
        $historyId = UserReadingHistory::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->value('id');
        if (!$historyId) {
            return [
                'novel_id'   => (string)$novel['novel_uuid'],
                'chapter_id' => null,
                'title'      => null,
                'progress'   => 0.0,
            ];
        }
        $chapterInfo = (new \app\model\Chapter())->infoById((int)UserReadingHistory::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->value('chapter_id'), 'chapter_uuid as chapter_id,title');
        return [
            'novel_id'   => (string)$novel['novel_uuid'],
            'chapter_id' => $chapterInfo['chapter_id'] ?? null,
            'title'      => $chapterInfo['title'] ?? null,
            'progress'   => (float) UserReadingHistory::where('user_id', $userId)
                ->where('novel_id', (int)$novel['id'])
                ->value('progress'),
        ];
    }

    public static function saveProgress(int $userId, string $novelUuid, string $chapterUuid, int $progress): array
    {
        $novel = NovelService::getInfoByUuid($novelUuid, 'id,novel_uuid');
        $chapter = ChapterService::getInfoByUuid($chapterUuid, 'id,chapter_uuid,title');
        
        if (!$chapter) {
            throw new HttpException(404, '章节不存在');
        }
        $historyId = UserReadingHistory::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->value('id');
        $data = [
            'user_id'     => $userId,
            'novel_id'    => (int)$novel['id'],
            'chapter_id'  => (int)$chapter['id'],
            'progress'    => max(0, min(100, $progress)),
            'last_read_at'=> date('Y-m-d H:i:s'),
        ];
        if ($historyId) {
            (new UserReadingHistory())->writeById((int)$historyId, $data);
        } else {
            (new UserReadingHistory())->writeById(0, $data);
            UserStatsService::incReadNovelCount($userId, 1);
        }
        return [
            'novel_id'   => (string)$novel['novel_uuid'],
            'chapter_id' => (string)$chapter['chapter_uuid'],
            'title'      => (string)$chapter['title'],
            'progress'   => $data['progress'],
        ];
    }
    /**
     * 写入阅读记录
     */
    public static function writeHistory(int $userId, string $novelUuid, string $chapterUuid, bool $finished, int $durationSec): array
    {
        $novel = NovelService::getInfoByUuid($novelUuid, 'id,novel_uuid');
        $chapter = ChapterService::getInfoByUuid($chapterUuid, 'id,chapter_uuid,title');
        
        if (!$chapter) {
            throw new HttpException(404, '章节不存在');
        }
        $historyId = UserReadLog::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->where('chapter_id', (int)$chapter['id'])
            ->value('id');
        (new UserReadLog())->writeById($historyId ?? 0, [
            'user_id'      => $userId,
            'novel_id'     => (int)$novel['id'],
            'chapter_id'   => (int)$chapter['id'],  
            'start_time'   => $historyId ?? null ?: date('Y-m-d H:i:s'),
            'end_time'     => $historyId ? date('Y-m-d H:i:s') : null,
            'duration_sec' => $durationSec,
            'device_id'    => request()->header('X-Device-Id', ''),
            'client_type'  => request()->header('Client-Type', ''),
            'ip'           => request()->ip(),
        ]);
        
        if ($durationSec > 0) {
            UserStatsService::incReadMinutes($userId, $durationSec);
        }
        $historyId = UserReadingHistory::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->value('id');
        $data = [
            'user_id'     => $userId,
            'novel_id'    => (int)$novel['id'],
            'chapter_id'  => (int)$chapter['id'],  
            'progress'    => $finished ? 100 : 0,
            'last_read_at'=> date('Y-m-d H:i:s'),
        ];
        if ($historyId) {
            (new UserReadingHistory())->writeById((int)$historyId, $data);
        } else {
            try {
                \app\service\StatsService::incReadCount(1);
            } catch (\Throwable $e) {
            }
            (new UserReadingHistory())->writeById(0, $data);
            UserStatsService::incReadNovelCount($userId, 1);
        }
        return [
            'novel_id'    => (string)$novel['novel_uuid'],
            'chapter_id'  => (string)$chapter['chapter_uuid'],
            'progress'    => $data['progress'],
            'last_read_at'=> $data['last_read_at'],
        ];
    }
}
