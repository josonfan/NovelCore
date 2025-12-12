<?php
namespace app\command;

use app\common\model\Sites;
use app\common\model\SiteInitRecord;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class SyncInit extends Command
{
    protected function configure()
    {
        $this->setName('sync:init')->setDescription('Initialize sync tasks for a site')
            ->addArgument('site_id', Argument::REQUIRED, 'Target site id');
    }

    protected function execute(Input $input, Output $output)
    {
        $siteId = (int)$input->getArgument('site_id');
        $site = (new Sites())->cacheInfo($siteId);
        if (empty($site)) {
            $output->writeln('site not found');
            return false;
        }
        $types = (array)(config('sync.enable_types') ?? []);
        if (empty($types)) {
            $output->writeln('no enabled types in config(sync.enable_types), exit');
            return true;
        }
        foreach ($types as $t) {
            $rec = new SiteInitRecord([
                'site_id' => $siteId,
                'status' => 'queued',
                'type' => $t,
                'last_pk' => 0,
            ]);
            $rec->save();
        }
        $output->writeln('queued init records for site ' . $siteId);
        return true;
    }
}
