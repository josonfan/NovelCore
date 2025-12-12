<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\UserProfileStats;
use think\Request;

class UserSummary extends BaseController
{
    /**
     * 我的页统计
     *
     * 路由：`GET /api/user/summary`
     * 鉴权：需登录
     * 返回：收藏数、阅读数、评论数、累计/周阅读分钟与活跃天数
     *
     * @param Request $request
     * @return \think\Response
     */
    public function summary(Request $request)
    {
        $user = $request->user;
        if (!$user) {
            return api_response(401, '未登录', [])->code(401);
        }
        $stats = UserProfileStats::find($user->id);
        $data = [
            'favorite_novel_count' => $stats->favorite_novel_count ?? 0,
            'read_novel_count'     => $stats->read_novel_count ?? 0,
            'comment_count'        => $stats->comment_count ?? 0,
            'total_read_minutes'   => $stats->total_read_minutes ?? 0,
            'week_read_minutes'    => $stats->week_read_minutes ?? 0,
            'week_active_days'     => $stats->week_active_days ?? 0,
        ];
        return api_response(200, '成功', $data);
    }
}
