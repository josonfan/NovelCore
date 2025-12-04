<?php
declare(strict_types=1);

namespace app\command;

use app\model\DailyStats;
use app\model\UserLoginLog;
use app\model\UserProfileStats;
use app\model\UserReadLog;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 重算最近7天的周统计：week_read_minutes、week_active_days。
 */
class UserWeekStatsRebuild extends Command
{
    protected function configure()
    {
        $this->setName('user:week-rebuild')
            ->setDescription('Rebuild week_read_minutes and week_active_days for all users (last 7 days)');
    }

    protected function execute(Input $input, Output $output)
    {
        $end   = date('Y-m-d 23:59:59');
        $start = date('Y-m-d 00:00:00', strtotime('-6 days')); // 包含今天在内的7天

        // 阅读时长汇总
        $readDurations = UserReadLog::whereBetweenTime('start_time', $start, $end)
            ->where('user_id', '>', 0)
            ->group('user_id')
            ->column('SUM(duration_sec)', 'user_id');

        // 活跃天数：阅读或登录的独立日期数量
        $activeDays = $this->collectActiveDays($start, $end);

        $total = 0;
        foreach ($this->mergeUserIds(array_keys($readDurations), array_keys($activeDays)) as $userId) {
            $weekMinutes = isset($readDurations[$userId]) ? (int) floor(((int) $readDurations[$userId]) / 60) : 0;
            $weekActive  = (int) ($activeDays[$userId] ?? 0);
            $this->upsertWeekStats($userId, $weekMinutes, $weekActive);
            $total++;
        }

        $output->writeln("Rebuilt week stats for {$total} users, range {$start} ~ {$end}");
        return self::SUCCESS;
    }

    protected function collectActiveDays(string $start, string $end): array
    {
        $days = [];

        // 阅读日志活跃日
        $readDays = UserReadLog::whereBetweenTime('start_time', $start, $end)
            ->where('user_id', '>', 0)
            ->fieldRaw('user_id, DATE(start_time) as d')
            ->select()
            ->toArray();
        foreach ($readDays as $row) {
            $days[$row['user_id']][$row['d']] = true;
        }

        // 登录日志活跃日
        $loginDays = UserLoginLog::whereBetweenTime('login_time', $start, $end)
            ->where('user_id', '>', 0)
            ->fieldRaw('user_id, DATE(login_time) as d')
            ->select()
            ->toArray();
        foreach ($loginDays as $row) {
            $days[$row['user_id']][$row['d']] = true;
        }

        // 计算每个用户的天数
        $counts = [];
        foreach ($days as $uid => $dates) {
            $counts[$uid] = count($dates);
        }

        return $counts;
    }

    protected function upsertWeekStats(int $userId, int $weekMinutes, int $weekActive): void
    {
        $table = (new UserProfileStats())->getTable();
        $sql = sprintf(
            "INSERT INTO %s (user_id, week_read_minutes, week_active_days, created_at, updated_at)
             VALUES (:user_id, :minutes, :active, NOW(), NOW())
             ON DUPLICATE KEY UPDATE week_read_minutes = :minutes, week_active_days = :active, updated_at = NOW()",
            $table
        );

        app('db')->query($sql, [
            'user_id' => $userId,
            'minutes' => $weekMinutes,
            'active'  => $weekActive,
        ]);
    }

    protected function mergeUserIds(array $a, array $b): array
    {
        return array_unique(array_merge($a, $b));
    }
}
