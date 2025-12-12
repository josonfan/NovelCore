<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Novel;
use app\service\ContentService;
use app\model\UserNovelFavorite;
use app\service\UserStatsService;
use think\Request;

class Favorite extends BaseController
{
    /**
     * 收藏小说
     *
     * 路由：`POST /api/novels/:novelId/favorite`
     * 鉴权：需登录
     * 参数：`:novelId` 外部 `novel_uuid`
     * 返回：`{ novel_id, favorited:true }`
     *
     * @param string $novelId
     * @param Request $request
     * @return \think\Response
     */
    public function favorite(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = ContentService::getNovelOrFail($novelId);
        $existsId = UserNovelFavorite::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->value('id');
        if (!$existsId) {
            (new UserNovelFavorite())->writeById(0, [
                'user_id'  => $user->id,
                'novel_id' => $novel->id,
            ]);
            (new Novel())->writeById((int)$novel->id, [
                'fav_count' => (int)($novel->fav_count ?? 0) + 1,
            ]);
            UserStatsService::incFavoriteCount($user->id, 1);
        }
        return api_response(200, '收藏成功', [
            'novel_id'  => $novel->novel_uuid,
            'favorited' => true,
        ]);
    }

    /**
     * 取消收藏
     *
     * 路由：`DELETE /api/novels/:novelId/favorite`
     * 鉴权：需登录
     * 参数：`:novelId` 外部 `novel_uuid`
     * 返回：`{ novel_id, favorited:false }`
     *
     * @param string $novelId
     * @param Request $request
     * @return \think\Response
     */
    public function unfavorite(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = ContentService::getNovelOrFail($novelId);
        $favoriteId = UserNovelFavorite::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->value('id');
        if ($favoriteId) {
            (new UserNovelFavorite())->deleteById((int)$favoriteId);
            $newCount = max(0, (int)($novel->fav_count ?? 0) - 1);
            (new Novel())->writeById((int)$novel->id, [
                'fav_count' => $newCount,
            ]);
            UserStatsService::incFavoriteCount($user->id, -1);
        }
        return api_response(200, '已取消收藏', [
            'novel_id'  => $novel->novel_uuid,
            'favorited' => false,
        ]);
    }

    /**
     * 收藏列表
     *
     * 路由：`GET /api/user/favorites`
     * 鉴权：需登录
     * 分页：`page`、`limit`（默认10，最大50）
     * 返回：列表与总数
     *
     * @param Request $request
     * @return \think\Response
     */
    public function list(Request $request)
    {
        $user = $request->user;
        $page     = max(1, (int) $request->get('page', 1));
        $pageSize = min(50, max(1, (int) $request->get('limit', 10)));
        $result = \app\service\ContentService::listFavorites($user->id, $page, $pageSize);
        return api_response(200, '成功', $result['list'], (int)$result['total']);
    }

    
}
