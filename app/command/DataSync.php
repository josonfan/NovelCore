<?php
declare(strict_types=1);

namespace app\command;

use app\service\DataSyncService;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

/**
 * 定时数据上报命令，占位实现。
 *
 * 用法示例：
 * php think data:sync --since="2025-12-01 00:00:00"
 */
class DataSync extends Command
{
    protected function configure()
    {
        $this->setName('data:sync')
            ->addOption('since', null, Option::VALUE_REQUIRED, '增量起始时间 (Y-m-d H:i:s)')
            ->setDescription('Collect updated users/orders/comments since given time and report');
    }

    protected function execute(Input $input, Output $output)
    {
        $since = (string) $input->getOption('since');
        if ($since === '') {
            $output->writeln('<error>请提供 --since 参数，例如 2025-12-01 00:00:00</error>');
            return self::FAILURE;
        }

        /** @var DataSyncService $service */
        $service = app(DataSyncService::class);
        $payload = $service->collectAll($since);

        // 预留上报，当前仅输出统计，实际推送请实现 DataSyncService::report
        $service->report($payload);

        $output->writeln(sprintf(
            'Collected users:%d orders:%d comments:%d since %s',
            count($payload['users']),
            count($payload['orders']),
            count($payload['comments']),
            $since
        ));

        return self::SUCCESS;
    }
}
