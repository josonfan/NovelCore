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
        
        $query = Novel::with(['author'])
            ->whereLike('title', "%{$keyword}%")
            ->whereOr('intro', 'like', "%{$keyword}%");

        if (!empty($options['category_id'])) {
            $query->where('category_id', (int) $options['category_id']);
        }
        if (!empty($options['status'])) {
            $query->where('status', (int) $options['status']);
        }
        $order = $options['order'] ?? 'id';
        $allowedOrder = ['id', 'created_at', 'view_count', 'like_count', 'fav_count'];
        if (!in_array($order, $allowedOrder, true)) {
            $order = 'id';
        }
        $query->order($order, 'desc');

        $paginator = $query->paginate([
            'list_rows' => $pageSize,
            'page'      => $page,
        ]);
        

        $storage = app(StorageService::class);
        $list = [];
        foreach ($paginator->items() as $novel) {
            $list[] = [
                'novel_id'   => $novel->novel_uuid,
                'title'      => $novel->title,
                'author'     => $novel->author->nickname ?? $novel->author->username ?? '',
                'intro'      => $novel->intro,
                'cover_url'  => $storage->getPublicUrl((string) $novel->cover),
                'status'     => (int) $novel->status,
                'is_vip'     => (int) $novel->is_vip,
                'word_count' => (int) $novel->word_count,
            ];
        }

        return [
            'total' => $paginator->total(),
            'list'  => $list,
        ];
    }
}
