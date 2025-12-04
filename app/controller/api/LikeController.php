<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Novel;
use app\model\UserNovelLike;
use think\Request;

/**
 * 小说点赞相关接口，外部 ID 使用 novel_uuid。
 */
class LikeController extends BaseController
{
    /**
     * 点赞小说，幂等：已点赞则直接返回 liked=true。
     */
    public function like(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = $this->findNovelByUuid($novelId);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $exists = UserNovelLike::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->find();

        if (!$exists) {
            // 唯一索引保证幂等；重复请求不会产生多条记录
            UserNovelLike::create([
                'user_id'  => $user->id,
                'novel_id' => $novel->id,
            ]);
            Novel::where('id', $novel->id)->inc('like_count')->update();
        }

        return json_success([
            'novel_id' => $novel->novel_uuid,
            'liked'    => true,
        ], '点赞成功');
    }

    /**
     * 取消点赞小说，幂等：未点赞直接返回 liked=false。
     */
    public function unlike(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = $this->findNovelByUuid($novelId);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $deleted = UserNovelLike::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->delete();

        if ($deleted) {
            Novel::where('id', $novel->id)
                ->where('like_count', '>', 0)
                ->dec('like_count')
                ->update();
        }

        return json_success([
            'novel_id' => $novel->novel_uuid,
            'liked'    => false,
        ], '已取消点赞');
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
