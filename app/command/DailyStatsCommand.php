<?php
declare(strict_types=1);

namespace app\command;

use app\model\DailyStats;
use app\model\Order;
use app\model\User;
use app\model\UserReadLog;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

/**
 * 每日统计汇总命令：统计指定日期（默认昨日）的运营数据。
 */
class DailyStatsCommand extends Command
{
    protected function configure()
    {
        $this->setName('daily:stats')
            ->addOption('date', null, Option::VALUE_REQUIRED, '统计日期，格式 YYYY-MM-DD，默认昨日')
            ->setDescription('Aggregate daily stats and store to daily_stats table');
    }

    protected function execute(Input $input, Output $output)
    {
        $date = (string) $input->getOption('date');
        if ($date === '') {
            $date = date('Y-m-d', strtotime('-1 day'));
        }

        [$start, $end] = $this->buildDateRange($date);

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

        $data = [
            'stat_date'    => $date,
            'new_users'    => $newUsers,
            'active_users' => $activeUsers,
            'order_count'  => $orderCount,
            'order_amount' => $orderAmount,
            'top_novel_id' => $topNovelId,
        ];

        $this->upsertStats($date, $data);

        $output->writeln(sprintf(
            'Daily stats for %s => new_users:%d active_users:%d orders:%d amount:%.2f top_novel_id:%s',
            $date,
            $newUsers,
            $activeUsers,
            $orderCount,
            $orderAmount,
            $topNovelId ?: 'null'
        ));

        return self::SUCCESS;
    }

    /**
     * 构建日期范围。
     */
    protected function buildDateRange(string $date): array
    {
        $start = $date . ' 00:00:00';
        $end = $date . ' 23:59:59';
        return [$start, $end];
    }

    /**
     * 写入或更新 daily_stats。
     */
    protected function upsertStats(string $date, array $data): void
    {
        $record = DailyStats::where('stat_date', $date)->find();
        if ($record) {
            $record->save($data);
        } else {
            $model = new DailyStats();
            $model->save($data);
        }
    }
}
