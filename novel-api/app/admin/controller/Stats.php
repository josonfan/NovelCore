<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\BaseController;
use app\model\DailyStats;
use app\model\Order;
use app\model\User;
use app\model\UserReadLog;
use think\Request;

class Stats extends BaseController
{
    /**
     * 每日统计
     *
     * 路由：`GET /api/admin/stats/daily`
     * 鉴权：需 Admin Token
     * 参数：`date?`（YYYY-MM-DD，默认昨天）
     * 返回：已持久化数据或实时计算数据
     *
     * @param Request $request
     * @return \think\Response
     */
    public function getDailyStats(Request $request)
    {
        $date = trim((string) $request->get('date', ''));
        if ($date === '') {
            $date = date('Y-m-d', strtotime('-1 day'));
        }
        $statId = DailyStats::where('stat_date', $date)->value('id');
        if (!$statId) {
            $data = $this->computeStats($date);
            return api_response(200, '实时计算', $data);
        }
        $data = (new DailyStats())->infoById((int)$statId, 'id,stat_date,new_users,active_users,order_count,order_amount,top_novel_id');
        return api_response(200, '获取成功', $data);
    }

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
