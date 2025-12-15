<?php
namespace app\command;

use app\common\service\SyncExecutor;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class SyncInit extends Command
{
    protected $name = 'sync:init';
    protected $description = '初始化站点同步任务';
    protected function configure()
    {
        $this->setName('sync:init')->setDescription('初始化站点同步任务');
    }

    protected function execute(Input $input, Output $output)
    {
        $n = SyncExecutor::runInit(500);
        if ($n > 0) {
            $output->writeln('已提交初始化任务：' . $n . ' 条');
        } else {
            $output->writeln('暂无可提交的初始化记录');
        }
        return true;
    }
}
