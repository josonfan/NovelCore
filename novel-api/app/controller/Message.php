<?php
declare(strict_types=1);

namespace app\controller;

use app\service\MessageService;
use think\exception\ValidateException;

class Message extends Common
{
    /**
     * 消息列表
     * 路由：POST /api/Message/index
     * 鉴权：需登录
     * 入参：page, limit, type?, is_read?
     * 返回：data { list, count }
     */
    public function index()
    {
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $page  = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 20, 'intval');
        $type = $this->request->param('type', null, 'intval');
        $isRead = $this->request->param('is_read', null, 'intval');
        $res = MessageService::list($userId, (int)$page, (int)$limit, $type, $isRead);
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    /**
     * 消息详情（查看即标记已读）
     * 路由：POST /api/Message/info
     * 鉴权：需登录
     * 入参：id
     * 返回：data 对象
     */
    public function info()
    {
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $id = $this->request->param('id', 0, 'intval');
        if ($id <= 0) {
            throw new ValidateException('参数错误');
        }
        $info = MessageService::info($userId, (int)$id);
        return $this->ajaxReturn(200, '获取成功', $info);
    }
    
    /**
     * 未读消息数量
     * 路由：GET /api/Message/unreadCount
     * 鉴权：需登录
     * 入参：无
     * 返回：data { count }
     */
    public function unreadCount()
    {
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $count = MessageService::unreadCount($userId);
        return $this->ajaxReturn(200, '获取成功', ['count' => $count]);
    }
}

