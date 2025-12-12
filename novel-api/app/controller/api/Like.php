<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Novel;
use app\service\ContentService;
use app\model\UserNovelLike;
use think\Request;

class Like extends BaseController
{
    /**
     * 点赞小说
     *
     * 路由：`POST /api/novels/:novelId/like`
     * 鉴权：需登录
     * 参数：`:novelId` 外部 `novel_uuid`
     * 返回：`{ novel_id, liked:true }`
     *
     * @param string $novelId
     * @param Request $request
     * @return \think\Response
     */
    public function like(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = ContentService::getNovelOrFail($novelId);
        $existsId = UserNovelLike::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->value('id');
        if (!$existsId) {
            (new UserNovelLike())->writeById(0, [
                'user_id'  => $user->id,
                'novel_id' => $novel->id,
            ]);
            (new Novel())->writeById((int)$novel->id, [
                'like_count' => (int)($novel->like_count ?? 0) + 1,
            ]);
        }
        return api_response(200, '点赞成功', [
            'novel_id' => $novel->novel_uuid,
            'liked'    => true,
        ]);
    }

    /**
     * 取消点赞
     *
     * 路由：`DELETE /api/novels/:novelId/like`
     * 鉴权：需登录
     * 参数：`:novelId` 外部 `novel_uuid`
     * 返回：`{ novel_id, liked:false }`
     *
     * @param string $novelId
     * @param Request $request
     * @return \think\Response
     */
    public function unlike(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = ContentService::getNovelOrFail($novelId);
        $likeId = UserNovelLike::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->value('id');
        if ($likeId) {
            (new UserNovelLike())->deleteById((int)$likeId);
            $newCount = max(0, (int)($novel->like_count ?? 0) - 1);
            (new Novel())->writeById((int)$novel->id, [
                'like_count' => $newCount,
            ]);
        }
        return api_response(200, '已取消点赞', [
            'novel_id' => $novel->novel_uuid,
            'liked'    => false,
        ]);
    }

    
}
