<?php
namespace app\command;

use app\common\service\SyncExecutor;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class SyncPush extends Command
{
    protected $name = 'sync:push';
    protected $description = '推送同步任务到子站点';
    protected function configure()
    {
        $this->setName('sync:push')->setDescription('推送同步任务到子站点');
    }

    protected function execute(Input $input, Output $output)
    {
        $n = SyncExecutor::run(50);
        $output->writeln('pushed ' . $n . ' tasks');
        return true;
    }
}
