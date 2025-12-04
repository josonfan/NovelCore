<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Novel;
use app\model\User;
use app\model\UserFollowAuthor;
use app\model\UserFollowNovel;
use think\Request;

/**
 * 关注作者与小说，使用 uuid 作为小说外部 ID。
 */
class FollowController extends BaseController
{
    /**
     * 关注作者（作者即用户，使用用户自增 ID）。
     */
    public function followAuthor(int $authorId, Request $request)
    {
        $user = $request->user;
        $author = User::find($authorId);
        if (!$author) {
            return json_error('作者不存在', 404)->code(404);
        }

        $exists = UserFollowAuthor::where('user_id', $user->id)
            ->where('author_id', $author->id)
            ->find();

        if (!$exists) {
            UserFollowAuthor::create([
                'user_id'   => $user->id,
                'author_id' => $author->id,
            ]);
        }

        return json_success([
            'author_id' => $author->id,
            'followed'  => true,
        ], '关注成功');
    }

    /**
     * 取消关注作者。
     */
    public function unfollowAuthor(int $authorId, Request $request)
    {
        $user = $request->user;
        $author = User::find($authorId);
        if (!$author) {
            return json_error('作者不存在', 404)->code(404);
        }

        UserFollowAuthor::where('user_id', $user->id)
            ->where('author_id', $author->id)
            ->delete();

        return json_success([
            'author_id' => $author->id,
            'followed'  => false,
        ], '已取消关注');
    }

    /**
     * 关注小说，外部 ID 使用 novel_uuid。
     */
    public function followNovel(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = $this->findNovelByUuid($novelId);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $exists = UserFollowNovel::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->find();

        if (!$exists) {
            UserFollowNovel::create([
                'user_id'  => $user->id,
                'novel_id' => $novel->id,
            ]);
        }

        return json_success([
            'novel_id' => $novel->novel_uuid,
            'followed' => true,
        ], '关注成功');
    }

    /**
     * 取消关注小说。
     */
    public function unfollowNovel(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = $this->findNovelByUuid($novelId);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        UserFollowNovel::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->delete();

        return json_success([
            'novel_id' => $novel->novel_uuid,
            'followed' => false,
        ], '已取消关注');
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
