<?php
declare(strict_types=1);

namespace app\service\search;

use app\service\ConfigService;
use app\service\StorageService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use think\facade\Log;

class EsSearchService implements SearchServiceInterface
{
    protected Client $client;
    protected array $config;

    public function __construct(ConfigService $configService, array $config)
    {
        $this->config = $config;
        $baseUri = rtrim((string) ($config['host'] ?? ''), '/');
        if ($baseUri !== '' && !preg_match('#^https?://#i', $baseUri)) {
            $baseUri = 'http://' . $baseUri;
        }

        $options = [
            'base_uri' => $baseUri . '/',
            'timeout'  => 5,
        ];

        if (!empty($config['username'])) {
            $options['auth'] = [$config['username'], $config['password'] ?? ''];
        }

        $this->client = new Client($options);
    }

    public function searchNovels(string $keyword ,  int $page = 1, int $pageSize = 20, array $options = []): array
    {
        $page     = max(1, $page);
        $pageSize = min(50, max(1, $pageSize));

        $indexPrefix = $this->config['index_prefix'] ?? ($this->config['indexPrefix'] ?? '');
        $index = rtrim($indexPrefix, '-') . 'novels';
        $from  = ($page - 1) * $pageSize;
        $categoryId = (int) ($options['category_id'] ?? 0);
        $statusProvided = array_key_exists('status', $options) && $options['status'] !== null && $options['status'] !== '';
        $status = (int) ($options['status'] ?? -1);
        $orderKey = (string) ($options['order'] ?? '');
        $tagIdsRaw = $options['tag_ids'] ?? [];
        if (is_string($tagIdsRaw)) {
            $tagIds = array_values(array_filter(array_map('intval', explode(',', $tagIdsRaw)), fn($v) => $v > 0));
        } else {
            $tagIds = array_values(array_filter(array_map('intval', (array) $tagIdsRaw), fn($v) => $v > 0));
        }
        $filters = [];
        if ($categoryId > 0) {
            $filters[] = ['term' => ['category_id' => $categoryId]];
        }
        if ($statusProvided && $status >= 0) {
            $filters[] = ['term' => ['status' => $status]];
        }
        if (!empty($tagIds)) {
            $filters[] = ['terms' => ['tag_ids' => $tagIds]];
        }
        $body = [
            'from' => $from,
            'size' => $pageSize,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'multi_match' => [
                                'query'  => $keyword,
                                'fields' => ['title^3', 'intro'],
                            ],
                        ],
                    ],
                    'filter' => $filters,
                    'must_not' => $statusProvided ? [] : [
                        ['term' => ['status' => 0]],
                    ],
                ],
            ],
        ];

        // 排序：将前端传入的 order 映射到 ES 字段，避免未映射字段报错
        $orderKey = (string) ($options['order'] ?? '');
        $sortMap = [
            'newest'     => ['field' => 'id', 'type' => 'long'],
            'view_count' => ['field' => 'view_count', 'type' => 'long'],
            'like_count' => ['field' => 'like_count', 'type' => 'long'],
            'fav_count'  => ['field' => 'fav_count', 'type' => 'long'],
            'word_count' => ['field' => 'word_count', 'type' => 'long'],
        ];
        if (isset($sortMap[$orderKey])) {
            $sortField = $sortMap[$orderKey]['field'];
            $sortType  = $sortMap[$orderKey]['type'];
            $body['sort'] = [
                [$sortField => ['order' => 'desc', 'unmapped_type' => $sortType]],
            ];
        } else {
            $body['sort'] = [['_score' => ['order' => 'desc']]];
        }
        
        $list = [];
        $total = 0;
        try {
            $response = $this->client->post($index . '/_search', [
                'json' => $body,
            ]);
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
        } catch (RequestException $e) {
            $status = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $respBody = $e->getResponse() ? (string) $e->getResponse()->getBody() : '';
            $msg = json_encode([
                'endpoint' => $index . '/_search',
                'status'   => $status,
                'error'    => $e->getMessage(),
                'response' => $respBody,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Log::error('ES search error: ' . $msg);
        } catch (\Throwable $e) {
            $msg = json_encode([
                'endpoint' => $index . '/_search',
                'error'    => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            Log::error('ES search failed: ' . $msg);
        }

        return [
            'total' => $total,
            'list'  => $list,
        ];
    }
}
