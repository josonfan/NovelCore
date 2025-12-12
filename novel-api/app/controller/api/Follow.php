<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Novel;
use app\model\User;
use app\model\UserFollowAuthor;
use app\model\UserFollowNovel;
use think\Request;

class Follow extends BaseController
{
    /**
        * 关注作者
        *
        * 路由：`POST /api/authors/:authorId/follow`
        * 鉴权：需登录
        * 参数：`:authorId` 作者内部ID
        * 返回：`{ author_id, followed:true }`
        *
        * @param int $authorId
        * @param Request $request
        * @return \think\Response
        */
    public function followAuthor(int $authorId, Request $request)
    {
        $user = $request->user;
        \app\service\UserService::ensureUserExists($authorId);
        $authorIdVal = $authorId;
        $existsId = UserFollowAuthor::where('user_id', $user->id)
            ->where('author_id', $authorIdVal)
            ->value('id');
        if (!$existsId) {
            (new UserFollowAuthor())->writeById(0, [
                'user_id'   => $user->id,
                'author_id' => $authorIdVal,
            ]);
        }
        return api_response(200, '关注成功', ['author_id' => $authorIdVal, 'followed' => true]);
    }

    /**
     * 取消关注作者
     *
     * 路由：`DELETE /api/authors/:authorId/follow`
     * 鉴权：需登录
     * 参数：`:authorId` 作者内部ID
     * 返回：`{ author_id, followed:false }`
     *
     * @param int $authorId
     * @param Request $request
     * @return \think\Response
     */
    public function unfollowAuthor(int $authorId, Request $request)
    {
        $user = $request->user;
        \app\service\UserService::ensureUserExists($authorId);
        $authorIdVal = $authorId;
        $followId = UserFollowAuthor::where('user_id', $user->id)
            ->where('author_id', $authorIdVal)
            ->value('id');
        if ($followId) {
            (new UserFollowAuthor())->deleteById((int)$followId);
        }
        return api_response(200, '已取消关注', ['author_id' => $authorIdVal, 'followed' => false]);
    }

    /**
     * 关注小说
     *
     * 路由：`POST /api/novels/:novelId/follow`
     * 鉴权：需登录
     * 参数：`:novelId` 外部 `novel_uuid`
     * 返回：`{ novel_id, followed:true }`
     *
     * @param string $novelId
     * @param Request $request
     * @return \think\Response
     */
    public function followNovel(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = \app\service\ContentService::getNovelOrFail($novelId);
        $existsId = UserFollowNovel::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->value('id');
        if (!$existsId) {
            (new UserFollowNovel())->writeById(0, [
                'user_id'  => $user->id,
                'novel_id' => $novel->id,
            ]);
        }
        return api_response(200, '关注成功', ['novel_id' => $novel->novel_uuid, 'followed' => true]);
    }

    /**
     * 取消关注小说
     *
     * 路由：`DELETE /api/novels/:novelId/follow`
     * 鉴权：需登录
     * 参数：`:novelId` 外部 `novel_uuid`
     * 返回：`{ novel_id, followed:false }`
     *
     * @param string $novelId
     * @param Request $request
     * @return \think\Response
     */
    public function unfollowNovel(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = \app\service\ContentService::getNovelOrFail($novelId);
        $followId = UserFollowNovel::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->value('id');
        if ($followId) {
            (new UserFollowNovel())->deleteById((int)$followId);
        }
        return api_response(200, '已取消关注', ['novel_id' => $novel->novel_uuid, 'followed' => false]);
    }

    
}
