<?php
namespace app\admin\controller;

use app\admin\service\SiteFeedbacksService;

class SiteFeedbacks extends Backend
{
    /**
     * 列表查询
     */
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
        
        $feedbackNo = $this->request->param('feedback_no');
        if (!empty($feedbackNo)) {
            $where['feedback_no'] = (string)$feedbackNo;
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

        $field = 'id,site_id,feedback_no,user_id,type,content,contact,status,image_count,created_at,updated_at';
        $orderby = 'id desc';
        $res = SiteFeedbacksService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }

    /**
     * 详情查询
     */
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,site_id,feedback_no,user_id,type,content,contact,device_info,app_version,client_type,status,reply_content,reply_admin_id,reply_at,image_count,created_at,updated_at';
        $info = SiteFeedbacksService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '反馈不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }

    /**
     * 处理反馈
     */
    public function process()
    {
        $postField = 'id,status,reply_content';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        $status = (int)($data['status'] ?? 0);
        $reply = (string)($data['reply_content'] ?? '');
        $adminId = (int)($this->request->uid ?? 0);
        
        $ok = SiteFeedbacksService::process($id, $status, $reply, $adminId);
        return $this->ajaxReturn(200, '处理成功', ['id' => $id, 'success' => $ok]);
    }
        
    /**
     * 获取类型列表
     */
    public function types()
    {
        return $this->ajaxReturn(200, '成功', SiteFeedbacksService::types());
    }

    /**
     * 获取状态列表
     */
    public function statuses()
    {
        return $this->ajaxReturn(200, '成功', SiteFeedbacksService::statuses());
    }
}
