<?php
declare(strict_types=1);

namespace app\service\search;

interface SearchServiceInterface
{
    /**
     * 小说搜索
     *
     * @param string $keyword   搜索关键词
     * @param int    $page      页码，从 1 开始
     * @param int    $pageSize  每页条数
     * @param array  $options   扩展过滤/排序
     * @return array ['total' => int, 'list' => array]
     */
    public function searchNovels(string $keyword, int $page = 1, int $pageSize = 20, array $options = []): array;
}
