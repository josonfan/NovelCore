<?php
declare(strict_types=1);

namespace app\command;

use app\model\UserProfileStats;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Cache;

/**
 * 将 Redis 中的用户统计增量合并回 user_profile_stats。
 */
class UserStatsSync extends Command
{
    protected function configure()
    {
        $this->setName('user:stats-sync')
            ->setDescription('Sync user stats delta from Redis to user_profile_stats');
    }

    protected function execute(Input $input, Output $output)
    {
        $redis = Cache::store('redis')->handler();
        $cursor = null;
        $pattern = 'user:stats:delta:*';
        $processed = 0;

        do {
            [$cursor, $keys] = $redis->scan($cursor ?? 0, ['MATCH' => $pattern, 'COUNT' => 100]);
            if ($keys === false) {
                break;
            }

            foreach ($keys as $key) {
                $delta = $redis->hGetAll($key);
                if (empty($delta)) {
                    $redis->del($key);
                    continue;
                }
                $userId = (int) str_replace('user:stats:delta:', '', $key);
                $this->applyDelta($userId, $delta);
                $redis->del($key);
                $processed++;
            }
        } while ($cursor !== 0);

        $output->writeln("Processed {$processed} user stats delta keys.");
        return true;
    }

    /**
     * 将增量写入 user_profile_stats，使用 INSERT ON DUPLICATE KEY UPDATE。
     */
    protected function applyDelta(int $userId, array $delta): void
    {
        $fields = [
            'favorite_novel_count' => (int) ($delta['favorite_novel_count'] ?? 0),
            'read_novel_count'     => (int) ($delta['read_novel_count'] ?? 0),
            'comment_count'        => (int) ($delta['comment_count'] ?? 0),
            'total_read_minutes'   => (int) ($delta['total_read_minutes'] ?? 0),
        ];

        // 过滤掉全 0 的增量
        if (array_sum($fields) === 0) {
            return;
        }

        $current = UserProfileStats::where('user_id', $userId)->find();
        if ($current) {
            $data = [
                'favorite_novel_count' => (int)$current->favorite_novel_count + $fields['favorite_novel_count'],
                'read_novel_count'     => (int)$current->read_novel_count + $fields['read_novel_count'],
                'comment_count'        => (int)$current->comment_count + $fields['comment_count'],
                'total_read_minutes'   => (int)$current->total_read_minutes + $fields['total_read_minutes'],
                'user_id'              => $userId,
            ];
            (new UserProfileStats())->writeById((int)$current->id, $data);
        } else {
            $data = [
                'user_id'               => $userId,
                'favorite_novel_count'  => $fields['favorite_novel_count'],
                'read_novel_count'      => $fields['read_novel_count'],
                'comment_count'         => $fields['comment_count'],
                'total_read_minutes'    => $fields['total_read_minutes'],
            ];
            (new UserProfileStats())->writeById(0, $data);
        }
    }
}
