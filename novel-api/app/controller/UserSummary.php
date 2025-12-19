<?php
declare(strict_types=1);

namespace app\controller;

use app\service\UserSummaryService;

class UserSummary extends Common
{
    /**
     * 我的页统计
     * 路由：GET /api/UserSummary/summary
     * 鉴权：需登录
     * 入参：无
     * 返回：data 统计对象
     */
    public function summary()
    {
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId <= 0) {
            return $this->ajaxReturn(401, '未登录或令牌无效', [])->code(401);
        }
        $summary = UserSummaryService::summary($userId);
        return $this->ajaxReturn(200, '成功', $summary);
    }
}
