<?php
declare(strict_types=1);

namespace app\service\search;

use app\service\ConfigService;
use app\service\StorageService;
use GuzzleHttp\Client;
use think\facade\Log;

class EsSearchService implements SearchServiceInterface
{
    protected Client $client;
    protected array $config;

    public function __construct(ConfigService $configService, array $config)
    {
        $this->config = $config;
        $baseUri = rtrim((string) ($config['host'] ?? ''), '/');

        $options = [
            'base_uri' => $baseUri . '/',
            'timeout'  => 5,
        ];

        if (!empty($config['username'])) {
            $options['auth'] = [$config['username'], $config['password'] ?? ''];
        }

        $this->client = new Client($options);
    }

    public function searchNovels(string $keyword, int $page = 1, int $pageSize = 20, array $options = []): array
    {
        $page     = max(1, $page);
        $pageSize = min(50, max(1, $pageSize));

        $index = ($this->config['indexPrefix'] ?? '') . 'novels';
        $from  = ($page - 1) * $pageSize;

        $body = [
            'from' => $from,
            'size' => $pageSize,
            'query' => [
                'multi_match' => [
                    'query'  => $keyword,
                    'fields' => ['title^3', 'intro', 'author_name'],
                ],
            ],
        ];

        // 简单排序扩展
        if (!empty($options['order'])) {
            $body['sort'] = [
                [$options['order'] => ['order' => 'desc']],
            ];
        }

        $list = [];
        $total = 0;

        try {
            $response = $this->client->post($index . '/_search', ['json' => $body]);
            $data = json_decode((string) $response->getBody(), true);
            $total = (int) ($data['hits']['total']['value'] ?? 0);
            $hits  = $data['hits']['hits'] ?? [];

            $storage = app(StorageService::class);
            foreach ($hits as $hit) {
                $source = $hit['_source'] ?? [];
                $list[] = [
                    'novel_id'   => $source['novel_uuid'] ?? $hit['_id'] ?? '',
                    'title'      => $source['title'] ?? '',
                    'author'     => $source['author_name'] ?? '',
                    'intro'      => $source['intro'] ?? '',
                    'cover_url'  => $storage->getPublicUrl((string) ($source['cover'] ?? '')),
                    'status'     => (int) ($source['status'] ?? 0),
                    'is_vip'     => (int) ($source['is_vip'] ?? 0),
                    'word_count' => (int) ($source['word_count'] ?? 0),
                ];
            }
        } catch (\Throwable $e) {
            Log::error('ES search failed: ' . $e->getMessage());
            // 回退：返回空结果，或上层决定回退 MySQL
        }

        return [
            'total' => $total,
            'list'  => $list,
        ];
    }
}
