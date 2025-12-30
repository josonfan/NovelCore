<?php
declare(strict_types=1);

namespace app\controller;

use app\service\FeedbackService;
use think\exception\ValidateException;

class Feedback extends Common
{
    /**
     * 投诉建议提交
     * 路由：POST /api/Feedback/add
     * 鉴权：无需登录（登录则关联 user_id）
     * 入参：type（suggestion|usage_issue|pay_member）, content, contact?, attachments[最多3]
     * 返回：data 主表视图
     */
    public function add()
    {
        $payload = [
            'type'        => $this->request->param('type', '', 'trim'),
            'content'     => $this->request->param('content', '', 'trim'),
            'contact'     => $this->request->param('contact', '', 'trim'),
            'attachments' => $this->request->param('attachments') ?? [],
        ];
        $headers = array_change_key_case((array)$this->request->header(), CASE_LOWER);
        $userId = (int) ($this->request->user_id ?? 0);
        $res = FeedbackService::create($userId > 0 ? $userId : null, $payload, $headers);
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    /**
     * 我的投诉建议列表
     * 路由：POST /api/Feedback/list
     * 鉴权：需登录
     * 入参：page, limit, status?
     * 返回：data { list, count }
     */
    public function list()
    {
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $page   = $this->request->param('page', 1, 'intval');
        $limit  = $this->request->param('limit', 10, 'intval');
        $status = $this->request->param('status', null, 'intval');
        $res = FeedbackService::list($userId, $page, $limit, $status);
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    /**
     * 投诉建议详情
     * 路由：POST /api/Feedback/info
     * 鉴权：需登录
     * 入参：id
     * 返回：data 主表 + attachments
     */
    public function info()
    {
        $userId = (int) ($this->request->user_id ?? 0);
        $id = $this->request->param('id', 0, 'intval');
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        if ($id <= 0) {
            throw new ValidateException('参数错误');
        }
        $res = FeedbackService::info($userId, (int)$id);
        return $this->ajaxReturn(200, '获取成功', $res);
    }
    /**
     * 投诉建议类型
     * 路由：POST /api/Feedback/types
     * 鉴权：无需登录
     * 入参：无
     * 返回：data 类型列表
     */
    public function types()
    {
        $res = FeedbackService::types();
        return $this->ajaxReturn(200, '获取成功', $res);
    }
    /**
     * 投诉建议状态
     * 路由：POST /api/Feedback/statuses
     * 鉴权：无需登录
     * 入参：无
     * 返回：data 状态列表
     */
    public function statuses()
    {
        $res = FeedbackService::statuses();
        return $this->ajaxReturn(200, '获取成功', $res);
    }

}

