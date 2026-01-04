<?php
declare(strict_types=1);

namespace app\service;

use app\model\UserNovelLike;
use app\model\Novel as NovelModel;

class LikeService
{
    public static function likeNovel(int $userId, string $novelUuid): array
    {
        $novel = NovelService::getInfoByUuid($novelUuid, 'id,novel_uuid,like_count');
        $existsId = UserNovelLike::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->value('id');
        if (!$existsId) {
            (new UserNovelLike())->writeById(0, [
                'user_id'  => $userId,
                'novel_id' => (int)$novel['id'],
            ]);
            (new NovelModel())->writeById((int)$novel['id'], [
                'like_count' => (int)($novel['like_count'] ?? 0) + 1,
            ]);
        }else{
            $model = new UserNovelLike();
            $cacheLikeKey = $model->getCacheKey($existsId,'del');
            cache()->delete($cacheLikeKey);
        }
        return [
            'novel_id' => (string)$novel['novel_uuid'],
            'liked'    => true,
        ];
    }

    public static function unlikeNovel(int $userId, string $novelUuid): array
    {
        $novel = NovelService::getInfoByUuid($novelUuid, 'id,novel_uuid,like_count');
        $likeId = UserNovelLike::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->value('id');
        if ($likeId) {
            $model = new UserNovelLike();
            $data=['user_id' => (int)$userId,'novel_id' => (int)$novel['id']];
            $cacheLikeKey = $model->getCacheKey(md5(json_encode($data, JSON_UNESCAPED_UNICODE)),'upd');
            cache()->delete($cacheLikeKey);
            $model->deleteById($likeId);
            $newCount = max(0, (int)($novel['like_count'] ?? 0) - 1);
            (new NovelModel())->writeById((int)$novel['id'], [
                'like_count' => $newCount,
            ]);
        }
        return [
            'novel_id' => (string)$novel['novel_uuid'],
            'liked'    => false,
        ];
    }
    /**
     * 获取用户点赞的小说列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param string $orderby
     * @return array
     */
    public static function getList(array $where,$field, string $orderby, int $page, int $limit): array
    {
        $model = new UserNovelLike();
        $res = $model->getList($where, '*', $orderby, $limit, $page);
        foreach ($res['list'] as $key => $value) {
            $res['list'][$key] = NovelService::info((int)$value['novel_id'], $field);
        }
        return $res;
    }
}

