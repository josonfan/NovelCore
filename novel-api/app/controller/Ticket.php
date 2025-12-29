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
