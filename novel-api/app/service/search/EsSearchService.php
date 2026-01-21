<?php
declare(strict_types=1);

namespace app\service\search;

use app\service\ConfigService;
use app\service\StorageService;
use utils\ElasticService;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use think\facade\Log;

class EsSearchService implements SearchServiceInterface
{
    protected array $config;
    protected ConfigService $configService;
    protected ElasticService $elastic;

    public function __construct(ConfigService $configService, array $config)
    {
        $this->configService = $configService;
        $this->config = $config;
        $this->elastic = new ElasticService();
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
        $safeKeyword = trim($keyword);
        $queryBool = [
            'filter' => $filters,
            'must_not' => [],
        ];
        if ($safeKeyword === '') {
            $query = ['match_all' => (object)[]];
        } else {
            // 提升中文短语匹配与前缀匹配的准确度，并增加容错匹配
            $queryBool['should'] = [
                // 标题精确短语匹配优先
                [
                    'match_phrase' => [
                        'title' => [
                            'query' => $safeKeyword,
                            'boost' => 4,
                            'slop'  => 0,
                        ],
                    ],
                ],
                // 简介短语匹配次级
                [
                    'match_phrase' => [
                        'intro' => [
                            'query' => $safeKeyword,
                            'boost' => 2,
                            'slop'  => 0,
                        ],
                    ],
                ],
                // 多字段分词匹配，使用 AND 提升相关性
                [
                    'multi_match' => [
                        'query'  => $safeKeyword,
                        'fields' => ['title^3', 'intro'],
                        'type'   => 'best_fields',
                        'operator' => 'and',
                        'minimum_should_match' => '70%',
                    ],
                ],
                // 容错匹配：允许轻微差异（如缺字/近似）
                [
                    'multi_match' => [
                        'query'  => $safeKeyword,
                        'fields' => ['title^3', 'intro'],
                        'type'   => 'most_fields',
                        'fuzziness' => 'AUTO',
                        'minimum_should_match' => '60%',
                    ],
                ],
                // 标题前缀短语匹配，兼容“读水浒”这类词组前缀搜索
                [
                    'match_phrase_prefix' => [
                        'title' => [
                            'query' => $safeKeyword,
                            'boost' => 3,
                            'max_expansions' => 10,
                        ],
                    ],
                ],
                // 简化查询字符串匹配，支持前缀/通配分析（取决于分词器）
                [
                    'simple_query_string' => [
                        'query' => $safeKeyword,
                        'fields' => ['title^4', 'intro^2'],
                        'default_operator' => 'and',
                        'analyze_wildcard' => true,
                    ],
                ],
            ];
            $queryBool['minimum_should_match'] = 1;
            $query = ['bool' => $queryBool];
        }
        $body = [
            'from' => $from,
            'size' => $pageSize,
            'query' => $query,
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
            $results = $this->elastic->search('novels', $body);
            $data = $results->asArray();
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
                    'view_count' => (int) ($source['view_count'] ?? 0),
                    'like_count' => (int) ($source['like_count'] ?? 0),
                    'fav_count'  => (int) ($source['fav_count'] ?? 0),
                    'category_id' => (int) ($source['category_id'] ?? 0),
                    'is_r18'     => (int) ($source['is_r18'] ?? 0),
                    'cover_url'  => $storage->getPublicUrl((string) ($source['cover'] ?? '')),
                    'status'     => (int) ($source['status'] ?? 0),
                    'is_vip'     => (int) ($source['is_vip'] ?? 0),
                    'word_count' => (int) ($source['word_count'] ?? 0),
                ];
            }
        } catch (ClientResponseException|ServerResponseException $e) {
            $status = method_exists($e, 'getResponse') && $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $respBody = method_exists($e, 'getResponse') && $e->getResponse() ? (string) $e->getResponse()->getBody() : '';
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

        // 当 ES 返回空结果时，回退到 MySQL 搜索以保证有可用结果
        if ($total === 0 && $safeKeyword !== '') {
            $mysql = new MysqlSearchService($this->configService);
            return $mysql->searchNovels($safeKeyword, $page, $pageSize, $options);
        }

        return [
            'total' => $total,
            'list'  => $list,
        ];
    }
}
