<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\UserProfileStats;
use think\Request;

/**
 * 我的页面统计汇总接口。
 */
class UserSummaryController extends BaseController
{
    /**
     * GET /api/user/summary
     */
    public function summary(Request $request)
    {
        $user = $request->user;
        if (!$user) {
            return json_error('未登录', 401)->code(401);
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

        return json_success($data);
    }
}
