<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\BaseController;
use app\model\DailyStats;
use app\model\Order;
use app\model\User;
use app\model\UserReadLog;
use think\Request;

/**
 * 后台统计查询接口：按日汇总。
 */
class StatsController extends BaseController
{
    /**
     * 获取指定日期（默认昨日）的每日统计。若无记录则实时计算。
     */
    public function getDailyStats(Request $request)
    {
        $date = trim((string) $request->get('date', ''));
        if ($date === '') {
            $date = date('Y-m-d', strtotime('-1 day'));
        }

        $stat = DailyStats::where('stat_date', $date)->find();
        if (!$stat) {
            $data = $this->computeStats($date);
            return json_success($data, '实时计算');
        }

        return json_success($stat, '获取成功');
    }

    /**
     * 实时计算指定日期的统计数据（用于补拉）。
     */
    protected function computeStats(string $date): array
    {
        [$start, $end] = [$date . ' 00:00:00', $date . ' 23:59:59'];

        $newUsers = User::whereBetweenTime('created_at', $start, $end)->count();
        $activeUsers = UserReadLog::whereBetweenTime('start_time', $start, $end)
            ->where('user_id', '>', 0)
            ->distinct(true)
            ->count('user_id');

        $paidOrders = Order::whereBetweenTime('created_at', $start, $end)
            ->where('status', 'paid');
        $orderCount = (int) $paidOrders->count();
        $orderAmount = (float) $paidOrders->sum('amount');

        $topNovelId = UserReadLog::whereBetweenTime('start_time', $start, $end)
            ->where('novel_id', '>', 0)
            ->group('novel_id')
            ->orderRaw('COUNT(*) DESC')
            ->limit(1)
            ->value('novel_id');

        return [
            'stat_date'    => $date,
            'new_users'    => $newUsers,
            'active_users' => $activeUsers,
            'order_count'  => $orderCount,
            'order_amount' => $orderAmount,
            'top_novel_id' => $topNovelId,
        ];
    }
}
