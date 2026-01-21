<?php
declare(strict_types=1);

namespace app\service\search;

use app\model\Novel;
use app\service\ConfigService;
use app\service\StorageService;

class MysqlSearchService implements SearchServiceInterface
{
    protected ConfigService $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function searchNovels(string $keyword, int $page = 1, int $pageSize = 20, array $options = []): array
    {
        $page     = max(1, $page);
        $pageSize = min(50, max(1, $pageSize));
        $model = new Novel();
        $where=[];
        if (!empty($keyword)) {
            $where["title|intro"] = ['like', "%{$keyword}%"];
        }  

        if (!empty($options['category_id'])) {
            $where['category_id'] = (int) $options['category_id'];
        }
        if (!empty($options['status'])) {
            $where['status'] = (int) $options['status'];
        }
        $tagIds = $options['tag_ids'] ?? null;
        if (!empty($tagIds)) {
            $novelIds = \app\model\NovelTag::where('tag_id', 'in', $tagIds)->column('novel_id');
            $novelIds = array_values(array_unique(array_map('intval', $novelIds)));
            $where['id'] = ['in', !empty($novelIds) ? $novelIds : [-1]];
        }        
        $order = $options['order'] ?? 'id';
        $allowedOrder = ['id', 'created_at', 'view_count', 'like_count', 'fav_count'];
        if (!in_array($order, $allowedOrder, true)) {
            $order = 'id';
        }
        $orderBy = $order . ' desc';
        $res = $model->getList(
            formatWhere($where),
            'novel_uuid as novel_id,title,cover as cover_url,author_id,status,is_vip,word_count,category_id,is_r18,view_count,like_count,fav_count',
            $orderBy,            
            $pageSize,
            $page
        );

        $storage = app(StorageService::class);
        $authorModel = new \app\model\User();
        foreach ($res['list'] as $key => $novel) {
            $novel['cover_url'] = $storage->getPublicUrl((string) $novel['cover_url']);
            if(empty($novel['author_id'])){
                $novel['author'] = '';
            }else{
                $author = $authorModel->infoById((int)$novel['author_id'], 'nickname,username');
                $novel['author'] = $author['nickname'] ??  '';
            }
            unset($novel['author_id']);
            $res['list'][$key] = $novel;
        }

        return $res;
    }
}
