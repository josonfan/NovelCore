<?php
declare(strict_types=1);

namespace app\controller;

use app\model\Comment as CommentModel;
use app\model\CommentLike;
use app\service\ContentService;
use app\service\CommentService;
use app\service\UserStatsService;
use app\service\UserService;
use app\service\ChapterService;
use app\service\NovelService;



class Comment extends Common
{
    /**
     * 评论列表
     * 路由：POST /api/Comment/index
     * 鉴权：无需登录
     * 入参：novelId（外部 novel_uuid）, page, limit, order
     * 筛选：`order`（默认desc）
     * view（默认flat）flat 扁平列表 thread 对话/楼中楼模式（需指定rootId） tree 树形结构
     * 返回：data { list, count }
     */
    public function index()
    {
        $novelId = $this->request->param('novelId', '', 'trim');
        $novel = \app\service\NovelService::getInfoByUuid($novelId, 'id,novel_uuid');
        $page     = $this->request->param('page', 1, 'intval');
        $pageSize = $this->request->param('limit', 10, 'intval');
        $order    = $this->request->param('order', 'desc', 'trim');
        $view     = $this->request->param('view', 'flat', 'trim');
        $rootId   = $this->request->param('rootId', 0, 'intval');
        $where = [
            'novel_id' => (int)$novel['id'],
            'status' => 1,
        ];
        $orderBy = $order === 'asc' ? 'id ASC' : 'id DESC';
        $fields = 'id,novel_id,chapter_id,parent_id,root_id,content,is_r18,like_count,status,review_source,created_at,user_id';
        $res = CommentService::list(formatWhere($where), $fields, $page, $pageSize, $orderBy);
        $list = $view === 'tree'
            ? \app\service\CommentViewService::formatTree($res['list'] ?? [])
            : \app\service\CommentViewService::formatList($res['list'] ?? []);
        if ($view === 'thread' && $rootId > 0) {
            $list = \app\service\CommentViewService::fetchThread((int)$novel['id'], $rootId);
        }
        return $this->ajaxReturn(200, '获取成功', [
            'list'  => $list,
            'count' => (int)$res['count'],
        ]);
    }

    /**
     * 发表评论
     * 路由：POST /api/Comment/store
     * 鉴权：需登录
     * 入参：novelId（外部 novel_uuid）, content, parent_id?
     * 返回：data 评论草稿信息（待审核）
     */
    public function store()
    {
        $novelId = $this->request->param('novelId', '', 'trim');
        $userId = (int) ($this->request->user_id ?? 0);
        $user = (new UserService())->info($userId);
        $novel = \app\service\NovelService::getInfoByUuid($novelId, 'id,novel_uuid');
        $content = (string) $this->request->post('content', '');
        $parentId = (int) $this->request->post('parent_id', 0);
        $isR18 = 0;
        $validated = CommentService::validateStore((int)$novel['id'], $userId, $content, $parentId);
        $rootId = $validated['root_id'];
        $status = (int)($validated['status'] ?? 0);
        $cm = new CommentModel();
        $cid = $cm->writeById(0, [
            'novel_id'      => (int)$novel['id'],
            'chapter_id'    => null,
            'user_id'       => $userId,
            'parent_id'     => $parentId ?: null,
            'root_id'       => $rootId,
            'content'       => $validated['content'],
            'is_r18'        => $isR18,
            'status'        => $status,
            'review_source' => 0,
        ]);
        UserStatsService::incCommentCount($userId, 1);
        CommentService::report([
            'id' => null,
            'content' => $validated['content'],
            'novel_id' => (int)$novel['id'],
            'chapter_id' => null,
            'user_id' => $userId,
            'is_r18' => $isR18,
        ]);

        if ($status === 1) {
             CommentService::sendReplyNotification($userId, (int)$cid, $parentId, (int)$novel['id'], $content, $user);
        }

        $msg = $status === 1 ? lang('评论成功') : lang('评论已提交，审核中');
        return $this->ajaxReturn(200, $msg, [
            'id'         => (string)$cid,
            'novel_id'   => (string)$novel['novel_uuid'],
            'chapter_id' => null,
            'parent_id'  => $parentId ?: null,
            'root_id'    => $rootId,
            'content'    => $content,
            'is_r18'     => (int)$isR18,
            'like_count' => 0,
            'status'     => $status,
            'review_source' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'user'       => [
                'uid'      => $userId,
                'nickname' => $user['nickname'],
                'avatar'   => $user['avatar'],
            ],
        ]);
    }

    /**
     * 点赞评论
     * 路由：POST /api/Comment/like
     * 鉴权：需登录
     * 入参：commentId
     * 返回：data { comment_id, liked:true }
     */
    public function like()
    {
        $commentId = $this->request->param('commentId', 0, 'intval');
        $userId = (int) ($this->request->user_id ?? 0);        
        $data = \app\service\CommentLikeService::like($userId, $commentId);
        return $this->ajaxReturn(200, '点赞成功', $data);
    }

    /**
     * 取消点赞评论
     * 路由：POST /api/Comment/unlike
     * 鉴权：需登录
     * 入参：commentId
     * 返回：data { comment_id, liked:false }
     */
    public function unlike()
    {
        $commentId = $this->request->param('commentId', 0, 'intval');
        $userId = (int) ($this->request->user_id ?? 0);
        
        $data = \app\service\CommentLikeService::unlike($userId, $commentId);
        return $this->ajaxReturn(200, '已取消点赞', $data);
    }

    /**
     * 获取我的评论列表
     * 路由：GET /api/Comment/getMyList
     * 鉴权：需登录
     * 返回：data { comments:[] }
     */
    public function getMyList()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $userId = (int) ($this->request->user_id ?? 0);
        $orderby = 'created_at DESC';
        $fields = 'id,novel_id,chapter_id,parent_id,root_id,content,is_r18,like_count,status,review_source,created_at';
        $where = [
            'user_id' => $userId,
        ];
        $data = \app\service\CommentService::list($where, $fields, $page, $limit, $orderby);
        $chapterService = new ChapterService();
        $novelService = new NovelService();
        foreach ($data['list'] as &$item) {
            if($item['novel_id']){
                $item['novel_info'] = $novelService->info($item['novel_id'], 'id,novel_uuid,title,cover,author,description,word_count,chapter_count,status,created_at');
            }else{
                $item['novel_info'] = null;
            }
            if ($item['chapter_id']) {
                $item['chapter_info'] = $chapterService->info($item['chapter_id'], 'id,novel_id,chapter_index,chapter_title,created_at');
            }else{
                $item['chapter_info'] = null;
            }
        }
        return $this->ajaxReturn(200, '获取成功', $data);
    }
}
