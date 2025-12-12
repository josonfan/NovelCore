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
        $novel = Novel::where('novel_uuid', $novelId)->find();
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

    public static function listNovels(array $filters, int $page, int $limit): array
    {
        $order = $filters['order'] ?? 'newest';
        if ($page < 1 || $limit < 1) {
            throw new ValidateException('分页参数不合法');
        }
        $orderMap = $order === 'popular'
            ? ['view_count' => 'desc', 'fav_count' => 'desc', 'id' => 'desc']
            : ['created_at' => 'desc', 'id' => 'desc'];
        $query = Novel::order($orderMap);
        if (!empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }
        if (!empty($filters['tag_id'])) {
            $ids = Db::name('novel_tags')->where('tag_id', (int) $filters['tag_id'])->column('novel_id');
            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            } else {
                $query->whereRaw('1=0');
            }
        }
        $paginator = $query->paginate(['list_rows' => $limit, 'page' => $page]);
        $ids = array_map(fn($item) => $item['id'], $paginator->items());
        $fields = 'novel_uuid as id,title,cover,intro,status,is_vip,word_count,updated_at';
        $storage = app(\app\service\StorageService::class);
        $model = new Novel();
        $list = [];
        foreach ($ids as $id) {
            $row = $model->infoById($id, $fields);
            $row['cover'] = $storage->getPublicUrl((string) ($row['cover'] ?? ''));
            $list[] = $row;
        }
        return ['list' => $list, 'total' => (int) $paginator->total()];
    }

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
        $chapterFields = 'chapter_uuid as id,title,is_free,is_vip,word_count,sort_order';
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
        if ($page < 1 || $limit < 1) {
            throw new ValidateException('分页参数不合法');
        }
        $paginator = \app\model\UserNovelFavorite::where('user_id', $userId)
            ->order('id', 'desc')
            ->paginate(['list_rows' => $limit, 'page' => $page]);
        $storage = app(\app\service\StorageService::class);
        $fields = 'novel_uuid as id,title,cover,intro,word_count,status,is_vip,updated_at';
        $novelModel = new Novel();
        $list = [];
        foreach ($paginator->items() as $fav) {
            $row = $novelModel->infoById((int)$fav['novel_id'], $fields);
            $row['cover'] = $storage->getPublicUrl((string) ($row['cover'] ?? ''));
            $row['favorite_id'] = $fav['id'];
            $list[] = $row;
        }
        return ['list' => $list, 'total' => (int) $paginator->total()];
    }

    public static function listReadingHistory(int $userId, int $page, int $limit): array
    {
        if ($page < 1 || $limit < 1) {
            throw new ValidateException('分页参数不合法');
        }
        $paginator = \app\model\UserReadingHistory::where('user_id', $userId)
            ->order('last_read_at', 'desc')
            ->paginate(['list_rows' => $limit, 'page' => $page]);
        $storage = app(\app\service\StorageService::class);
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

    public static function listChapters(int $novelPk, int $page, int $limit): array
    {
        if ($page < 1 || $limit < 1) {
            throw new ValidateException('分页参数不合法');
        }
        $query = Chapter::where('novel_id', $novelPk)->order('sort_order', 'asc');
        $paginator = $query->paginate(['list_rows' => $limit, 'page' => $page]);
        $chapters = [];
        foreach ($paginator->items() as $chapter) {
            $chapters[] = [
                'id'         => $chapter->chapter_uuid,
                'title'      => $chapter->title,
                'is_free'    => (int) $chapter->is_free,
                'is_vip'     => (int) $chapter->is_vip,
                'price'      => (int) $chapter->price,
                'word_count' => (int) $chapter->word_count,
                'sort_order' => (int) $chapter->sort_order,
            ];
        }
        return ['list' => $chapters, 'total' => (int) $paginator->total()];
    }
}
