<?php
declare(strict_types=1);

namespace app\controller;


class Follow extends Common
{
    /**
     * 关注作者
     * 路由：POST /api/Follow/followAuthor
     * 鉴权：需登录
     * 入参：authorId
     * 返回：code=200
     */
    public function followAuthor()
    {
        $authorId = $this->request->param('authorId', 0, 'intval');
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $data = \app\service\FollowService::followAuthor($userId, $authorId);
        return $this->ajaxReturn(200, '关注成功', $data);
    }
    /**
     * 取消关注作者
     * 路由：POST /api/Follow/unfollowAuthor
     * 鉴权：需登录
     * 入参：authorId
     * 返回：code=200
     */
    public function unfollowAuthor()
    {
        $authorId = $this->request->param('authorId', 0, 'intval');
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $data = \app\service\FollowService::unfollowAuthor($userId, $authorId);
        return $this->ajaxReturn(200, '已取消关注', $data);
    }
    /**
     * 关注小说
     * 路由：POST /api/Follow/followNovel
     * 鉴权：需登录
     * 入参：novelId（外部 novel_uuid）
     * 返回：code=200
     */
    public function followNovel()
    {
        $novelId = $this->request->param('novelId', '', 'trim');
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $data = \app\service\FollowService::followNovel($userId, $novelId);
        return $this->ajaxReturn(200, '关注成功', $data);
    }
    /**
     * 取消关注小说
     * 路由：POST /api/Follow/unfollowNovel
     * 鉴权：需登录
     * 入参：novelId（外部 novel_uuid）
     * 返回：code=200
     */
    public function unfollowNovel()
    {
        $novelId = $this->request->param('novelId', '', 'trim');
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $data = \app\service\FollowService::unfollowNovel($userId, $novelId);
        return $this->ajaxReturn(200, '已取消关注', $data);
    }
}
