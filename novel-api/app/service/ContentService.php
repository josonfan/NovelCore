<?php
declare(strict_types=1);

namespace app\service;

use app\model\Novel;
use app\model\Chapter;
use think\facade\Db;
use think\exception\ValidateException;

class ContentService
{
    public static function findNovelByExternalId(string $novelId): ?Novel
    {
        $m = new Novel();
        $novel = $m->get($novelId);
        if (!$novel && ctype_digit($novelId)) {
            $novel = Novel::find((int) $novelId);
        }
        return $novel;
    }

    public static function getNovelOrFail(string $novelId): Novel
    {
        $novel = self::findNovelByExternalId($novelId);
        if (!$novel) {
            throw new ValidateException('小说不存在');
        }
        return $novel;
    }

    public static function findChapterByExternalId(Novel $novel, string $chapterId): ?Chapter
    {
        $chapter = Chapter::where('novel_id', $novel->id)
            ->where('chapter_uuid', $chapterId)
            ->find();
        if (!$chapter && ctype_digit($chapterId)) {
            $chapter = Chapter::where('novel_id', $novel->id)->find((int) $chapterId);
        }
        return $chapter;
    }

    public static function getChapterOrFail(Novel $novel, string $chapterId): Chapter
    {
        $chapter = self::findChapterByExternalId($novel, $chapterId);
        if (!$chapter) {
            throw new ValidateException('章节不存在');
        }
        return $chapter;
    }

    // 小说列表已迁移至 NovelService::listNovels

    public static function novelDetail(string|int $novelId, int $previewLimit = 20): array
    {
        $novelPk = is_int($novelId) ? $novelId : Novel::where('novel_uuid', $novelId)->value('id');
        if (!$novelPk) {
            throw new ValidateException('小说不存在');
        }
        $fields = 'novel_uuid as id,title,cover,intro,status,is_vip,word_count,like_count,fav_count,view_count,created_at,updated_at';
        $storage = app(\app\service\StorageService::class);
        $model = new Novel();
        $row = $model->infoById((int)$novelPk, $fields);
        $row['cover'] = $storage->getPublicUrl((string) ($row['cover'] ?? ''));
        $chapterIds = Chapter::where('novel_id', $novelPk)->order('sort_order', 'asc')->limit($previewLimit)->column('id');
        $chapters = [];
        $chapterFields = 'chapter_uuid,id,title,is_free,is_vip,word_count,sort_order';
        $chapterModel = new Chapter();
        foreach ($chapterIds as $cid) {
            $chapters[] = $chapterModel->infoById((int)$cid, $chapterFields);
        }
        $row['chapter_count'] = Chapter::where('novel_id', $novelPk)->count();
        $row['chapters_preview'] = $chapters;
        return $row;
    }

    public static function listFavorites(int $userId, int $page, int $limit): array
    {
        return \app\service\FavoriteService::getListByUser($userId, $page, $limit);
    }

    public static function listReadingHistory(int $userId, int $page, int $limit): array
    {
        return \app\service\ReadingHistoryService::getListByUser($userId, $page, $limit);
    }

    public static function listChaptersByQuery($where, $order, $fields='*', int $page=1, int $limit=20): array
    {
        $chapterModel = new Chapter();
        return $chapterModel->getList($where, $fields, $order, $limit, $page);
    }

    public static function listChapters(int $novelPk, int $page, int $limit): array
    {
        $fields = 'chapter_uuid as id,title,is_free,is_vip,price,word_count,sort_order';
        return self::listChaptersByQuery(
            ['novel_id' => $novelPk],
            'sort_order asc',
            $fields,
            $page,
            $limit
        );
    }
}
