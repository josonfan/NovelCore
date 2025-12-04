<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Novel;
use app\model\UserNovelFavorite;
use app\service\UserStatsService;
use think\Request;

/**
 * 小说收藏（书架）接口，外部 ID 使用 novel_uuid。
 */
class FavoriteController extends BaseController
{
    /**
     * 收藏小说，幂等：已收藏直接返回。
     */
    public function favorite(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = $this->findNovelByUuid($novelId);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $exists = UserNovelFavorite::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->find();

        if (!$exists) {
            UserNovelFavorite::create([
                'user_id'  => $user->id,
                'novel_id' => $novel->id,
            ]);
            Novel::where('id', $novel->id)->inc('fav_count')->update();
            UserStatsService::incFavoriteCount($user->id, 1);
        }

        return json_success([
            'novel_id'  => $novel->novel_uuid,
            'favorited' => true,
        ], '收藏成功');
    }

    /**
     * 取消收藏，幂等：未收藏直接返回。
     */
    public function unfavorite(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = $this->findNovelByUuid($novelId);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $deleted = UserNovelFavorite::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->delete();

        if ($deleted) {
            Novel::where('id', $novel->id)
                ->where('fav_count', '>', 0)
                ->dec('fav_count')
                ->update();
            UserStatsService::incFavoriteCount($user->id, -1);
        }

        return json_success([
            'novel_id'  => $novel->novel_uuid,
            'favorited' => false,
        ], '已取消收藏');
    }

    /**
     * 收藏列表（书架），含分页。
     */
    public function list(Request $request)
    {
        $user = $request->user;
        $page     = max(1, (int) $request->get('page', 1));
        $pageSize = min(50, max(1, (int) $request->get('page_size', 10)));

        $query = UserNovelFavorite::alias('f')
            ->join('novels n', 'f.novel_id = n.id')
            ->where('f.user_id', $user->id)
            ->order('f.id', 'desc')
            ->field([
                'f.id',
                'n.novel_uuid',
                'n.title',
                'n.cover',
                'n.intro',
                'n.word_count',
                'n.status',
                'n.is_vip',
                'n.updated_at',
            ]);

        $paginator = $query->paginate([
            'list_rows' => $pageSize,
            'page'      => $page,
        ]);

        $storage = app(\app\service\StorageService::class);
        $favorites = [];
        foreach ($paginator->items() as $item) {
            $favorites[] = [
                // 外部 ID 使用 novel_uuid
                'id'          => $item['novel_uuid'],
                'title'       => $item['title'],
                'cover'       => $storage->getPublicUrl((string) $item['cover']),
                'intro'       => $item['intro'],
                'word_count'  => (int) $item['word_count'],
                'status'      => (int) $item['status'],
                'is_vip'      => (int) $item['is_vip'],
                'updated_at'  => $item['updated_at'],
                'favorite_id' => $item['id'],
            ];
        }

        return json_success([
            'total'      => $paginator->total(),
            'page'       => $paginator->currentPage(),
            'page_size'  => $paginator->listRows(),
            'favorites'  => $favorites,
        ], '获取成功');
    }

    /**
     * 按 uuid 查找小说，必要时兼容自增 id。
     */
    protected function findNovelByUuid(string $novelId): ?Novel
    {
        $novel = Novel::where('novel_uuid', $novelId)->find();
        if (!$novel && ctype_digit($novelId)) {
            $novel = Novel::find((int) $novelId);
        }
        return $novel;
    }

}
