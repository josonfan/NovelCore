<?php
declare(strict_types=1);

namespace app\controller;

use app\service\ContentService;
use app\model\UserNovelFavorite;
use app\service\UserStatsService;

class Favorite extends Common
{
    /**
     * 收藏小说
     * 路由：POST /api/Favorite/favorite
     * 鉴权：需登录
     * 入参：novelId（外部 novel_uuid）
     * 返回：data { novel_id, favorited }
     */
    public function favorite()
    {
        $novelId = $this->request->param('novelId', '', 'trim');
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $novel = \app\service\NovelService::getInfoByUuid($novelId, 'id,novel_uuid,fav_count');
        $existsId = UserNovelFavorite::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->value('id');
        if (!$existsId) {
            (new UserNovelFavorite())->writeById(0, [
                'user_id'  => $userId,
                'novel_id' => (int)$novel['id'],
            ]);
            (new \app\model\Novel())->writeById((int)$novel['id'], [
                'fav_count' => (int)($novel['fav_count'] ?? 0) + 1,
            ]);
            UserStatsService::incFavoriteCount($userId, 1);
        }
        return $this->ajaxReturn(200, '收藏成功', [
            'novel_id'  => (string)$novel['novel_uuid'],
            'favorited' => true,
        ]);
    }

    /**
     * 取消收藏
     * 路由：POST /api/Favorite/unfavorite
     * 鉴权：需登录
     * 入参：novelId（外部 novel_uuid）
     * 返回：data { novel_id, favorited:false }
     */
    public function unfavorite()
    {
        $novelId = $this->request->param('novelId', '', 'trim');
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $novel = \app\service\NovelService::getInfoByUuid($novelId, 'id,novel_uuid,fav_count');
        $favoriteId = UserNovelFavorite::where('user_id', $userId)  
            ->where('novel_id', (int)$novel['id'])
            ->value('id');
        if ($favoriteId) {
            (new UserNovelFavorite())->deleteById((int)$favoriteId);
            $newCount = max(0, (int)($novel['fav_count'] ?? 0) - 1);
            (new \app\model\Novel())->writeById((int)$novel['id'], [
                'fav_count' => $newCount,
            ]);
            UserStatsService::incFavoriteCount($userId, -1);
        }
        return $this->ajaxReturn(200, '已取消收藏', [
            'novel_id'  => (string)$novel['novel_uuid'],
            'favorited' => false,
        ]);
    }

    /**
     * 收藏列表
     * 路由：POST /api/Favorite/list
     * 鉴权：需登录
     * 入参：page, limit
     * 返回：data { list, count }
     */
    public function list()
    {
        $user_id = $this->request->user_id;
        $page     = $this->request->param('page', 1, 'intval');
        $pageSize = $this->request->param('limit', 10, 'intval');
        $result = \app\service\FavoriteService::getListByUser($user_id, $page, $pageSize);
        return $this->ajaxReturn(200, '获取成功', [
            'list'  => $result['list'],
            'count' => (int)$result['count'],
        ]);
    }
}
