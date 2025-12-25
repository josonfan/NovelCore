<?php
declare(strict_types=1);

namespace app\service;

use app\model\Novel;
use app\model\UserNovelLike;
use app\model\UserNovelFavorite;
use think\facade\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;



class NovelService
{
    /**
     * 获取小说详情（主键或外部已映射的主键）
     * - 统一使用模型方法 `infoById` 读取详情
     * - 不做跨表聚合，仅返回主表视图字段
     *
     * @param  $id    主键ID（字符串形式，兼容控制器映射后的数值ID）
     * @param  $fields 视图字段列表，支持 `field as alias`，默认 '*'
     * @return array 详情视图数据
     * @throws DataNotFoundException 当资源不存在时抛出 404 业务异常
     */
    public static function info($id, string $fields = '*', bool $isIncreaseViewCount = false): array
    {
        $m = new Novel();
        $novel = $m->infoById($id, $fields);
        if (empty($novel)) {
            throw new DataNotFoundException('小说不存在');
        }
        $storage = new StorageService();
        $novel['cover'] = $storage->getPublicUrl((string) ($novel['cover'] ?? ''));
        if (!$isIncreaseViewCount) {
            return $novel;
        }
        $newCount = max(0, (int)($novel['view_count'] ?? 0) + 1);
        $m->writeById($id, [
            'view_count' => $newCount,
        ]);
        $novel['view_count'] = $newCount;
        return $novel;
    }

    /**
     * 获取小说列表（统一模型列表方法）
     * - 列表查询统一使用模型的 `getList($where,$field,$orderby,$limit,$page)`
     * - Service 只处理 `novels` 主表，不做跨表拼装
     *
     * @param array  $where   查询条件（使用 `formatWhere` 转换后传入）
     * @param string $fields  列表视图字段
     * @param string $order   排序（如 'created_at desc, id desc'）
     * @param int    $page    页码
     * @param int    $limit   每页数量
     * @return array { list: array, count: int }
     */
    public static function getList(array $where, string $fields = '*', string $order = '', int $page = 1, int $limit = 20): array
    {
        $m = new Novel();
        $storage = new StorageService();
        $row = $m->getList($where, $fields, $order, $limit, $page);
        $list = [];
        foreach ($row['list'] as $fav) {
            $fav['cover'] = $storage->getPublicUrl((string) ($fav['cover'] ?? ''));
            $list[] = $fav;
        }
        return ['list' => $list, 'count' => $row['count']];
    }

    /**
     * 通过外部 `novel_uuid` 获取小说详情
     * - 将外部ID映射为主键后，统一调用 `infoById`
     *
     * @param string $uuid   外部 `novel_uuid`
     * @param string $fields 视图字段列表
     * @return array 详情视图数据
     * @throws DataNotFoundException 当资源不存在时抛出 404 业务异常
     */
    public static function getInfoByUuid(string $uuid, string $fields = '*'): array
    {
        $m = new Novel();
        $pk = $m->getPk();
        $id = $m->where('novel_uuid', $uuid)->value($pk);
        if (!$id) {
            throw new DataNotFoundException('小说不存在');
        }
        return self::info($id, $fields, true);       
    }

    public static function getUserStatus(string $uuid, int $userId): array
    {
        $m = new Novel();
        $pk = $m->getPk();
        $id = $m->where('novel_uuid', $uuid)->value($pk);
        $isLiked = 0;
        $isFavorited = 0;
        if ($userId > 0 && $id) {
            $data=['user_id' => (int)$userId,'novel_id' => (int)$id];
            $userNovelLike = (new \app\model\UserNovelLike());
            $cacheLikeKey = $userNovelLike->getCacheKey(md5(json_encode($data, JSON_UNESCAPED_UNICODE)),'upd');
            $cacheLike = $userNovelLike->getCacheStatus($cacheLikeKey);
            if($cacheLike){
                $likedId = true;
            }else{
                $likedId = $userNovelLike->where('user_id', $userId)->where('novel_id', $id)->value('id');
                $cacheLikeKey = $userNovelLike->getCacheKey($likedId,'del');
                $cacheLike = $userNovelLike->getCacheStatus($cacheLikeKey);
                if($cacheLike){
                    $likedId = false;
                }
            }
            
            $userNovelFavorite = (new \app\model\UserNovelFavorite());
            $cacheFavoriteKey = $userNovelFavorite->getCacheKey(md5(json_encode($data, JSON_UNESCAPED_UNICODE)),'upd');
            $cacheFavorite = $userNovelFavorite->getCacheStatus($cacheFavoriteKey);
            if($cacheFavorite){
                $favId = true;
            }else{
                $favId = $userNovelFavorite->where('user_id', $userId)->where('novel_id', $id)->value('id');
                $cacheFavoriteKey = $userNovelFavorite->getCacheKey($favId,'del');
                $cacheFavorite = $userNovelFavorite->getCacheStatus($cacheFavoriteKey);
                if($cacheFavorite){
                    $favId = false;
                }
            } 
            $isLiked = $likedId ? 1 : 0;
            $isFavorited = $favId ? 1 : 0;
        }
        return ['is_liked' => (int)$isLiked, 'is_favorited' => (int)$isFavorited];
    }
}
