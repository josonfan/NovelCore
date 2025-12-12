<?php
declare(strict_types=1);

namespace app\command;

use app\service\ReportSyncService;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class SyncUserOrder extends Command
{
    protected function configure()
    {
        $this->setName('sync:user-order')
            ->addOption('since', null, Option::VALUE_REQUIRED, '增量起始时间，格式 Y-m-d H:i:s，可选')
            ->setDescription('Sync updated users and orders to admin backend');
    }

    protected function execute(Input $input, Output $output)
    {
        $since = (string) $input->getOption('since');
        $service = app(ReportSyncService::class);
        try {
            $service->sync($since ?: null);
            $output->writeln('<info>Sync completed</info>');
            return true;
        } catch (\Throwable $e) {
            $output->writeln('<error>Sync failed: ' . $e->getMessage() . '</error>');
            return false;
        }
    }
}
