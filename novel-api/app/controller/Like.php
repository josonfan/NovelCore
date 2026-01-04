<?php
declare(strict_types=1);

namespace app\controller;


class Like extends Common
{
    /**
     * 点赞小说
     * 路由：POST /api/Like/like
     * 鉴权：需登录
     * 入参：novelId（外部 novel_uuid）
     * 返回：data { liked:true }
     */
    public function like()
    {
        $novelId = $this->request->param('novelId', '', 'trim');
        $userId = (int) ($this->request->user_id ?? 0);
       
        $data = \app\service\LikeService::likeNovel($userId, $novelId);
        return $this->ajaxReturn(200, '点赞成功', $data);
    }
    /**
     * 取消点赞
     * 路由：POST /api/Like/unlike
     * 鉴权：需登录
     * 入参：novelId（外部 novel_uuid）
     * 返回：data { liked:false }
     */
    public function unlike()
    {
        $novelId = $this->request->param('novelId', '', 'trim');
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $data = \app\service\LikeService::unlikeNovel($userId, $novelId);
        return $this->ajaxReturn(200, '已取消点赞', $data);
    }
    /**
     * 获取用户点赞的小说列表
     * 路由：GET /api/Like/getList
     * 鉴权：需登录
     * 返回：data { novels:[] }
     */
    public function getList()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $where['user_id'] = (int) ($this->request->user_id ?? 0);
        $orderby = 'created_at DESC';
        $fields = 'novel_uuid as id,title,category_id,cover,intro,author_name,is_r18,status,is_vip,word_count,updated_at';
        $data = \app\service\LikeService::getList(formatWhere($where), $fields, $orderby, $page, $limit);
        return $this->ajaxReturn(200, '获取成功', $data);
    }
}
