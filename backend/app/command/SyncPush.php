<?php
namespace app\command;

use app\common\service\SyncExecutor;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class SyncPush extends Command
{
    protected function configure()
    {
        $this->setName('sync:push')->setDescription('Push pending sync tasks to sites');
    }

    protected function execute(Input $input, Output $output)
    {
        $n = SyncExecutor::run(50);
        $output->writeln('pushed ' . $n . ' tasks');
        return true;
    }
}
