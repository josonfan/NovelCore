<?php
namespace app\command;

use app\common\model\Sites;
use app\common\model\Categories;
use app\common\model\Tags;
use app\common\model\Novels;
use app\common\model\Chapters;
use app\common\model\SiteInitRecord;
use app\common\service\SyncService;
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
        $counts = ['category' => 0, 'tag' => 0, 'novel' => 0, 'chapter' => 0];
        $cats = (new Categories())->where('is_active', 1)->field('id')->select()->toArray();
        foreach ($cats as $row) { SyncService::enqueueForSite($siteId, 'category', (int)$row['id'], 'create'); $counts['category']++; }
        $tags = (new Tags())->where('is_active', 1)->field('id')->select()->toArray();
        foreach ($tags as $row) { SyncService::enqueueForSite($siteId, 'tag', (int)$row['id'], 'create'); $counts['tag']++; }
        $novels = (new Novels())->field('id')->select()->toArray();
        foreach ($novels as $row) { SyncService::enqueueForSite($siteId, 'novel', (int)$row['id'], 'create'); $counts['novel']++; }
        $chapters = (new Chapters())->field('id')->select()->toArray();
        foreach ($chapters as $row) { SyncService::enqueueForSite($siteId, 'chapter', (int)$row['id'], 'create'); $counts['chapter']++; }
        $rec = new SiteInitRecord([
            'site_id' => $siteId,
            'status' => 'queued',
            'category_count' => $counts['category'],
            'tag_count' => $counts['tag'],
            'novel_count' => $counts['novel'],
            'chapter_count' => $counts['chapter'],
        ]);
        $rec->save();
        $output->writeln('queued init tasks for site ' . $siteId);
        return true;
    }
}
