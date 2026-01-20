<?php
declare(strict_types=1);

namespace app\service;

use app\model\Chapter;
use app\model\Novel;
use app\model\UserReadingHistory;
use app\model\UserNovelFavorite;
use app\model\UserNovelLike;

class FavoriteService
{
    public static function getListByUser(int $userId, int $page = 1, int $limit = 10): array
    {
        $model = new UserNovelFavorite();
        $res = $model->getList(
            ['user_id' => $userId],
            'id,novel_id',
            'id desc',
            $limit,
            $page
        );
        $storage = new \app\service\StorageService();
        $fields = 'novel_uuid as id,title,cover,intro,word_count,like_count,status,is_vip,category_id,is_r18,updated_at';
        $novelModel = new Novel();
        $novelIds = array_values(array_unique(array_map('intval', array_column($res['list'] ?? [], 'novel_id'))));
        $progressByNovelId = [];
        $chapterById = [];
        $likedNovelIds = [];
        if (!empty($novelIds)) {
            $progressRows = UserReadingHistory::where('user_id', $userId)
                ->whereIn('novel_id', $novelIds)
                ->field('novel_id,chapter_id,progress')
                ->select()
                ->toArray();
            $chapterIds = [];
            foreach ($progressRows as $progressRow) {
                $novelPk = (int)($progressRow['novel_id'] ?? 0);
                if ($novelPk <= 0) {
                    continue;
                }
                $progressByNovelId[$novelPk] = $progressRow;
                $chapterId = (int)($progressRow['chapter_id'] ?? 0);
                if ($chapterId > 0) {
                    $chapterIds[] = $chapterId;
                }
            }
            $chapterIds = array_values(array_unique($chapterIds));
            if (!empty($chapterIds)) {
                $chapterRows = (new Chapter())
                    ->whereIn('id', $chapterIds)
                    ->field('id,chapter_uuid,title')
                    ->select()
                    ->toArray();
                foreach ($chapterRows as $chapterRow) {
                    $chapterById[(int)$chapterRow['id']] = $chapterRow;
                }
            }

            $userNovelLike = new UserNovelLike();
            $likedRows = $userNovelLike->where('user_id', $userId)
                ->whereIn('novel_id', $novelIds)
                ->field('id,novel_id')
                ->select()
                ->toArray();
            $likeIdByNovelId = [];
            foreach ($likedRows as $likedRow) {
                $novelPk = (int)($likedRow['novel_id'] ?? 0);
                $likeId = (int)($likedRow['id'] ?? 0);
                if ($novelPk > 0 && $likeId > 0) {
                    $likeIdByNovelId[$novelPk] = $likeId;
                }
            }

            foreach ($novelIds as $novelId) {
                $data = ['user_id' => (int)$userId, 'novel_id' => (int)$novelId];
                $cacheLikeKey = $userNovelLike->getCacheKey(md5(json_encode($data, JSON_UNESCAPED_UNICODE)), 'upd');
                if ($userNovelLike->getCacheStatus($cacheLikeKey)) {
                    $likedNovelIds[(int)$novelId] = 1;
                    continue;
                }

                $likedId = $likeIdByNovelId[(int)$novelId] ?? 0;
                if ($likedId <= 0) {
                    continue;
                }
                $cacheLikeKey = $userNovelLike->getCacheKey($likedId, 'del');
                if (!$userNovelLike->getCacheStatus($cacheLikeKey)) {
                    $likedNovelIds[(int)$novelId] = 1;
                }
            }
        }

        $list = [];
        foreach ($res['list'] as $fav) {
            $row = $novelModel->infoById((int)$fav['novel_id'], $fields);
            $row['cover'] = $storage->getPublicUrl((string) ($row['cover'] ?? ''));
            $row['favorite_id'] = $fav['id'];
            $row['is_liked'] = isset($likedNovelIds[(int)$fav['novel_id']]) ? 1 : 0;
            $progressRow = $progressByNovelId[(int)$fav['novel_id']] ?? null;
            if (empty($progressRow)) {
                $row['reading_progress'] = [
                    'novel_id'   => (string)($row['id'] ?? ''),
                    'chapter_id' => null,
                    'title'      => null,
                    'progress'   => 0.0,
                ];
            } else {
                $chapterRow = $chapterById[(int)($progressRow['chapter_id'] ?? 0)] ?? null;
                $row['reading_progress'] = [
                    'novel_id'   => (string)($row['id'] ?? ''),
                    'chapter_id' => $chapterRow['chapter_uuid'] ?? null,
                    'title'      => $chapterRow['title'] ?? null,
                    'progress'   => (float)($progressRow['progress'] ?? 0),
                ];
            }
            $list[] = $row;
        }
        return ['list' => $list, 'count' => (int) $res['count']];
    }
}
