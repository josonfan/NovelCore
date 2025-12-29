<?php
namespace app\command;

use app\service\SyncExecutor;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class SyncPush extends Command
{
    protected $name = 'sync:push';
    protected $description = '推送同步任务到总站';
    protected function configure()
    {
        $this->setName('sync:push')->setDescription('推送同步任务到总站');
    }

    protected function execute(Input $input, Output $output)
    {
        $start = microtime(true);
        $n = SyncExecutor::run(500);
        $elapsedMs = (int)round((microtime(true) - $start) * 1000);
        $output->writeln('pushed ' . $n . ' tasks');
        $output->writeln('elapsed ' . $elapsedMs . ' ms');
        return true;
    }
}
