<?php
declare (strict_types = 1);

namespace app\command;
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
use app\common\model\Novels;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Cache;
use utils\ElasticService;
use app\common\model\NovelTags;

class PushToElastic extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('push:elastic')
            ->setDescription('定时推送数据到 Elasticsearch');       
    }

    /**
     * 执行指令
     * @param Input $input
     * @param Output $output
     * @return void
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    protected function execute(Input $input, Output $output)
    {
        // 获取数据
        $data = $this->getDataToPush();
        $settings = [
            'id' => ['type' => 'long'],
            'novel_uuid' => ['type' => 'text'],
            'title' => [
                'type' => 'text',
                'fields' => [
                    'raw' => ['type' => 'keyword']
                ]
            ],
            'author_name' => ['type' => 'text'],
            'category_id' => ['type' => 'integer'],
            'intro' => ['type' => 'text'],
            'is_r18' => ['type' => 'byte'],
            'is_vip' => ['type' => 'byte'],
            'like_count' => ['type' => 'integer'],
            'fav_count' => ['type' => 'integer'],
            'view_count' => ['type' => 'integer'],
            'status' => ['type' => 'byte'],
            'tag_ids' => ['type' => 'integer'],
            'updated_at' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss'  // 在创建索引时指定日期格式
            ],
        ];
        $model = new Novels();
        $index = $model->getName();
        // 推送到 Elasticsearch
        $elasticService = new ElasticService();
        $elasticService->createIndex($index, $settings);
        // 分离正常数据和需要删除的数据
        $toIndex = [];
        $toDelete = [];
        foreach ($data as $row) {            
            $toIndex[] = $row;            
        }

        // 同步数据到 Elasticsearch
        if(!empty($toIndex))$elasticService->bulkIndex($index,$toIndex,true);

        // 从 Elasticsearch 删除无效数据
        if(!empty($toDelete))$elasticService->bulkDelete($index,$toDelete,true);

        // 更新缓存的同步时间
        if(!empty($data)){
            $maxTime = max(array_column($data, 'updated_at'));
            Cache::set('last_up_es_time', $maxTime,0);
        }

        $output->writeln("数据推送完成！");
    }
    /**
     * 模拟获取要推送的数据
     */
    private function getDataToPush(): array
    {
        $model = new Novels();
        $lastSyncTime = Cache::get('last_up_es_time','1970-01-01 00:00:00');
        $list =  $model->where('updated_at', '>', $lastSyncTime)->field('id,novel_uuid,title,author_name,category_id,intro,is_r18,is_vip,like_count,fav_count,view_count,status,updated_at')->limit(100)->select()->toArray();
        foreach ($list as $key => $value) {
            $tagIds = (new NovelTags())->whereIn('novel_id', $value['id'])->column('tag_id');
            $list[$key]['tag_ids'] = $tagIds;
        }
        return $list;
    }
}
