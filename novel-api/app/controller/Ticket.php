<?php
declare(strict_types=1);

namespace app\controller;

use app\service\TicketService;
use think\exception\ValidateException;

class Ticket extends Common
{
    public function add()
    {        
        $userId = (int) $this->request->user_id;
        $payload = [
            'type'        => $this->request->param('type', '', 'trim'),
            'title'       => $this->request->param('title', '', 'trim'),
            'description' => $this->request->param('description', '', 'trim'),
            'work_id'     => $this->request->param('work_id', null),
            'contact'     => $this->request->param('contact', '', 'trim'),
            'priority'    => $this->request->param('priority', 0, 'intval'),
            'attachments' => $this->request->param('attachments') ?? [],
        ];
        $res = TicketService::create($userId, $payload);
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    /**
     * 工单回复
     * 路由：POST /api/Ticket/reply
     * 鉴权：需登录
     * 入参：ticket_id, content, attachments
     */
    public function reply()
    {
        $userId = (int) $this->request->user_id;
        $ticketId = $this->request->param('ticket_id', 0, 'intval');
        $content = $this->request->param('content', '', 'trim');
        $attachments = $this->request->param('attachments') ?? [];
        
        if ($ticketId <= 0) {
             throw new ValidateException('参数错误');
        }
        $res = TicketService::reply($userId, $ticketId, $content, is_array($attachments) ? $attachments : []);
        return $this->ajaxReturn(200, '回复成功', $res);
    }

    /**
     * 工单回复列表
     * 路由：POST /api/Ticket/replies
     * 鉴权：需登录
     * 入参：ticket_id
     * 返回：list 列表
     */
    public function replies()
    {
        $userId = (int) $this->request->user_id;
        $ticketId = $this->request->param('ticket_id', 0, 'intval');
        
        if ($ticketId <= 0) {
             throw new ValidateException('参数错误');
        }
        
        $list = TicketService::getReplies($userId, $ticketId);
        $res = [
            'list' => $list,
            'count' => count($list),
        ];
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    /**
     * 工单评价
     * 路由：POST /api/Ticket/evaluate
     * 鉴权：需登录
     * 入参：ticket_id, score, content
     */
    public function evaluate()
    {
        $userId = (int) $this->request->user_id;
        $ticketId = $this->request->param('ticket_id', 0, 'intval');
        $score = $this->request->param('score', 5, 'intval');
        $content = $this->request->param('content', '', 'trim');

        if ($ticketId <= 0) {
             throw new ValidateException('参数错误');
        }
        
        TicketService::evaluate($userId, $ticketId, $score, $content);
        return $this->ajaxReturn(200, '评价成功');
    }

    public function list()
    {
        $userId = (int) $this->request->user_id;
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $type = $this->request->param('type', '', 'trim');
        $status = $this->request->param('status', null);
        $status = $status === null ? null : intval($status);
        $res = TicketService::list($userId, $page, $limit, $type, $status);
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    public function info()
    {
        $userId = (int) $this->request->user_id;
        $id = $this->request->param('id', 0, 'intval');
        if ($id <= 0) {
            throw new ValidateException('参数错误');
        }
        $res = TicketService::info($userId, $id);
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    public function types()
    {
        $res = [
            'list' => TicketService::types(),
            'count' => 4,
        ];
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    public function statuses()
    {
        $list = TicketService::statuses();
        $res = [
            'list' => $list,
            'count' => count($list),
        ];
        return $this->ajaxReturn(200, '获取成功', $res);
    }
}
