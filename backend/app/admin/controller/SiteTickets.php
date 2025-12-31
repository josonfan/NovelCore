<?php
namespace app\admin\controller;

use app\admin\service\SiteTicketsService;

class SiteTickets extends Backend
{
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $siteId = $this->request->param('site_id');
        if ($siteId !== null && $siteId !== '') {
            $where['site_id'] = (int)$siteId;
        }
        $userId = $this->request->param('user_id');
        if ($userId !== null && $userId !== '') {
            $where['user_id'] = (int)$userId;
        }
        $type = $this->request->param('type');
        if (!empty($type)) {
            $where['type'] = (string)$type;
        }
        $status = $this->request->param('status');
        if ($status !== null && $status !== '') {
            $where['status'] = (int)$status;
        }
        $priority = $this->request->param('priority');
        if ($priority !== null && $priority !== '') {
            $where['priority'] = (int)$priority;
        }
        $ticketNo = $this->request->param('ticket_no');
        if (!empty($ticketNo)) {
            $where['ticket_no'] = (string)$ticketNo;
        }
        $workId = $this->request->param('work_id');
        if ($workId !== null && $workId !== '') {
            $where['work_id'] = (int)$workId;
        }
        $start = $this->request->param('start_date');
        $end = $this->request->param('end_date');
        if (!empty($start) && !empty($end)) {
            $where['created_at'] = ['between', [$start, $end]];
        } elseif (!empty($start)) {
            $where['created_at'] = ['>=', $start];
        } elseif (!empty($end)) {
            $where['created_at'] = ['<=', $end];
        }
        $field = 'id,site_id,ticket_id,ticket_no,user_id,type,title,work_id,contact,priority,status,created_at,updated_at';
        $orderby = 'id desc';
        $res = SiteTicketsService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }

    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,site_id,ticket_id,ticket_no,user_id,type,title,description,work_id,contact,priority,status,reply_content,reply_admin_id,reply_at,created_at,updated_at';
        $info = SiteTicketsService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '工单不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }

    public function process()
    {
        $postField = 'id,status,reply_content';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        $status = (int)($data['status'] ?? 0);
        $reply = (string)($data['reply_content'] ?? '');
        $adminId = (int)($this->request->uid ?? 0);
        $ok = SiteTicketsService::process($id, $status, $reply, $adminId);
        return $this->ajaxReturn(200, '处理成功', ['id' => $id, 'success' => $ok]);
    }
        
    public function types()
    {
        return $this->ajaxReturn(200, '成功', SiteTicketsService::types());
    }

    public function statuses()
    {
        return $this->ajaxReturn(200, '成功', SiteTicketsService::statuses());
    }

    public function reply()
    {
        $postField = 'id,content,attachments';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        $content = (string)($data['content'] ?? '');
        $attachments = (array)($data['attachments'] ?? []);
        $adminId = (int)($this->request->uid ?? 0);
        $ok = SiteTicketsService::reply($id, $content, $attachments, $adminId);
        return $this->ajaxReturn(200, '回复成功', ['success' => $ok]);
    }



    public function replies()
    {
        $id = (int)($this->request->param('id') ?? 0);
        if ($id <= 0) {
            return $this->ajaxReturn(400, '参数错误');
        }
        $list = SiteTicketsService::replies($id);
        return $this->ajaxReturn(200, '成功', $list);
    }

}
