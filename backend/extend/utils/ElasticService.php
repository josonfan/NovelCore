<?php

namespace utils;

use app\admin\service\ConfigWebService;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Promise\Promise;
use app\common\model\SearchConfig;

class ElasticService
{
    protected $client;
    protected $indexPrefix;

    /**
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     */
    public function __construct(int $siteId = 0)
    {
        $m = new SearchConfig();
        $id = $m->where('site_id', $siteId)->where('provider', 'elasticsearch')->where('is_active', 1)->value('id');
        if (empty($id)) {
            $id = $m->where('provider', 'elasticsearch')->where('is_active', 1)->value('id');
        }
        if (empty($id)) {
            throw new \Exception('未配置存储配置');
        }       
        $cfg = $m->infoById($id);
        $host = isset($cfg['host']) ? str_replace('`','', trim($cfg['host'])) : '';
        $username = $cfg['username'] ?? '';
        $password = $cfg['password'] ?? '';
        $this->indexPrefix = $cfg['index_prefix'] ?? '';
        $this->client = ClientBuilder::create()
            ->setHosts(["{$host}"])
            ->setBasicAuthentication($username, $password)
            ->build();
    }
    /**
     * 索引前缀处理
     * @param string $index
     * @return string
     */
    protected function idx(string $index): string
    {
        $p = trim((string)$this->indexPrefix);
        if ($p === '') return $index;
        if (strpos($index, $p) === 0) return $index;
        return $p . $index;
    }
    /**
     * 判断索引是否存在
     * @param string $index
     * @return bool|Elasticsearch|Promise
     * @throws ClientResponseException
     */
    public function isIndexExist(string $index)
    {        
        try {
            $res =  $this->client->indices()->exists(['index' => $index]);
            if ($res->getStatusCode() != 200) {
                return false;
            }
            return true;
        } catch (ClientResponseException $e) {
            // 如果发生异常，认为索引不存在
            return false;
        }
    }

    /**
     * 自动创建索引并设置映射
     * @param string $index
     * @param array $settings
     * @return bool
     * @throws ClientResponseException
     */
    public function createIndex(string $index, array $settings): bool
    {
        if (!$this->isIndexExist($this->idx($index))) {
            $params = [
                'index' => $this->idx($index),
                'body'  => [
                    'settings' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 1,
                    ],
                    'mappings' => [
                        'properties' => $settings,  // 设置字段映射
                    ],
                ],
            ];

            try {
                $this->client->indices()->create($params);
                return true;
            } catch (ClientResponseException $e) {
                return false;
            }
        }

        return true;
    }
    /**
     * 插入文档
     * @param string $index
     * @param array $document
     * @return Elasticsearch
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    public function insertDocument(string $index, array $document): Elasticsearch
    {

        $params = [
            'index' => $this->idx($index),
            'body'  => $document
        ];

        return $this->client->index($params);
    }

    /**
     * 批量索引数据（自动判断更新或插入）
     * @param string $index
     * @param array $data
     * @return Elasticsearch|false|Promise
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function bulkIndex(string $index,array $data,$is_echo=false)
    {
        if (empty($data)) return false;

        $bulkData = [];
        foreach ($data as $row) {
            $bulkData[] = [
                'update' => [
                    '_index' => $this->idx($index),
                    '_id' => $row['id'],
                ]
            ];
            $bulkData[] = [
                'doc' => $row, // 更新字段
                'doc_as_upsert' => true, // 如果不存在则插入
            ];
        }

        $response = $this->client->bulk(['body' => $bulkData]);

        if (isset($response['errors']) && $response['errors'] === true) {
            trace('Elasticsearch errors:'.json_encode($response['errors'],JSON_UNESCAPED_UNICODE));
            throw new \Exception('Error indexing data to Elasticsearch');
        }
        trace(count($data) . 'records indexed/updated successfully.');
        if($is_echo){
            echo count($data) . " records indexed/updated successfully.\n";
            
        }

        return $response;
    }

    /**
     * 批量删除数据
     * @param string $index
     * @param array $ids
     * @param bool $is_echo
     * @return Elasticsearch|false|Promise
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function bulkDelete(string $index,array $ids,$is_echo=false)
    {
        if (empty($ids)) return false;

        $bulkData = [];
        foreach ($ids as $id) {
            $bulkData[] = [
                'delete' => [
                    '_index' => $this->idx($index),
                    '_id' => $id,
                ]
            ];
        }

        $response = $this->client->bulk(['body' => $bulkData]);

        if (isset($response['errors']) && $response['errors'] === true) {
            trace('Elasticsearch errors:'.json_encode($response['errors'],JSON_UNESCAPED_UNICODE));
            throw new \Exception('Error deleting data from Elasticsearch');
        }
        if($is_echo){
            echo count($ids) . " records deleted successfully.\n";
        }
        trace(count($ids) . 'records deleted successfully.');
        return $response;
    }
    /**
     * 更新文档
     * @param string $index
     * @param string $id
     * @param array $data
     * @return Elasticsearch
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    public function updateDocument(string $index, string $id, array $data): Elasticsearch
    {
        $params = [
            'index' => $this->idx($index),
            'id'    => $id,
            'body'  => [
                'doc' => $data
            ]
        ];

        return $this->client->update($params);
    }

    /**
     * 删除文档
     * @param string $index
     * @param string $id
     * @return Elasticsearch
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    public function deleteDocument(string $index, string $id): Elasticsearch
    {
        $params = [
            'index' => $this->idx($index),
            'id'    => $id,
        ];

        return $this->client->delete($params);
    }

    /**
     * 搜索文档
     * @param string $index
     * @param array $query
     * @return Elasticsearch
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function search(string $index, array $query): Elasticsearch
    {
        $params = [
            'index' => $this->idx($index),
            'body'  => $query,
        ];

        return $this->client->search($params);
    }

    /**
     * 基于经纬度的距离查询
     * @param string $index
     * @param float $latitude
     * @param float $longitude
     * @param int $distance
     * @param array $query
     * @param int $page
     * @param int $pageSize
     * @param array $uids
     * @param string $sortField
     * @param string $sortOrder
     * @return array
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function searchByLocation(string $index, float $latitude, float $longitude, int $distance=100, array $query = [], int $page = 1, int $pageSize = 10, array $uids = [], string $sortField='_score', string $sortOrder='desc'): array
    {
        // 计算分页的起始位置
        $from = ($page - 1) * $pageSize;
        // 构建查询条件
        $body = [
            'query' => [
                'bool' => [
                    'must' => [], // 用于存储 `$query` 的条件
                    'filter' => [
                        [
                            'geo_distance' => [
                                'distance' => $distance . 'km',
                                'location' => [
                                    'lat' => $latitude,
                                    'lon' => $longitude,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '_source' => false,  // 不返回文档内容
            'from' => $from, // 分页起始位置
            'size' => $pageSize, // 每页文档数量
            'fields' => ['id'], // 只返回主键
            'script_fields' => [
                'distance' => [
                    'script' => [
                        'source' => "doc['location'].arcDistance($latitude, $longitude)",
                        'lang' => 'painless',
                    ],
                ],
            ],
            'sort' => [
                [
                    $sortField => [
                        'order' => $sortOrder, // 排序方式，默认为降序
                    ]
                ]
            ]
        ];
        if($sortField=='location'){
            $body['sort'] = [
                [
                    '_geo_distance' => [
                        'location' => [
                            'lat' => $latitude,
                            'lon' => $longitude,
                        ],
                        'order' => 'asc', // 升序排列
                        'unit' => 'km',   // 使用公里作为单位
                    ],
                ],
            ];

        }
        // 如果有额外的查询条件
        if (!empty($query)) {
            foreach ($query as $key => $value) {
                if(empty($value)) continue;
                $body['query']['bool']['must'][] = [
                    'match' => [
                        $key => $value,
                    ],
                ];
            }
        }
        // 如果提供了多个uids，则添加terms查询条件
        if (!empty($uids)) {
            $body['query']['bool']['must'][] = [
                'terms' => [
                    'uid' => $uids, // 查找多个uid
                ],
            ];
        }
        // 执行搜索
        $response = $this->client->search([
            'index' => $this->idx($index),
            'body' => $body,
        ]);

        $res['list'] = $response['hits']['hits']??[];
        $res['count'] = $response['hits']['total']['value']??0;
        return $res;
    }

    /**
     * 记录搜索关键词
     * @param string $index
     * @param $keyword
     * @return void
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    public function recordKeyword(string $index,$keyword)
    {
        $index = $this->idx($index.'_search_keyword');
        $settings = [
            'keyword' => [
                'type' => 'text',
                'fields' => [
                    'raw' => [
                        'type' => 'keyword'
                    ]
                ]
            ],
            'count' => [
                'type' => 'integer'
            ],
            'created_at' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss||epoch_millis'
            ]
        ];
        $this->createIndex($index, $settings);
        // 查询是否已存在该关键词
        $response = $this->client->search([
            'index' => $index,
            'body' => [
                'query' => [
                    'term' => ['keyword.raw' => $keyword],
                ]
            ]
        ]);
        if (!empty($response['hits']['hits'])) {
            // 如果存在，增加计数
            $docId = $response['hits']['hits'][0]['_id'];
            $currentCount = $response['hits']['hits'][0]['_source']['count'];
            $this->client->update([
                'index' => $index,
                'id'    => $docId,
                'body'  => [
                    'doc' => ['count' => $currentCount + 1],
                ]
            ]);
        } else {
            // 如果不存在，创建新记录
            $this->client->index([
                'index' => $index,
                'body'  => [
                    'keyword' => $keyword,
                    'count'   => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ]);
        }
    }

    /**
     * 获取热门关键词
     * @param string $index
     * @param int $size
     * @return array
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function getHotKeywords(string $index,int $size = 10): array
    {
        $index = $this->idx($index.'_search_keyword');
        $response = $this->client->search([
            'index' => $index,
            'body'  => [
                'size' => $size,
                'sort' => [
                    'count' => ['order' => 'desc'],
                ]
            ]
        ]);

        $hotKeywords = [];
        foreach ($response['hits']['hits'] as $hit) {
            $hotKeywords[] = [
                'keyword' => $hit['_source']['keyword'],
                'count'   => $hit['_source']['count'],
            ];
        }

        return $hotKeywords;
    }
}
