<?php
declare(strict_types=1);

namespace app\controller;

use app\service\ConfigService;
use app\service\SearchLogService;
use app\service\search\SearchServiceFactory;
use think\exception\ValidateException;

class Search extends Common
{
    /**
     * 小说搜索
     * 路由：POST /api/Search/index
     * 鉴权：无需登录
     * 入参：keyword, page, limit, category_id?, tag_ids?, status?, order?
     * 返回：data { list, count }
     */
    public function index()
    {
        $keyword  = $this->request->param('keyword', '', 'trim');
        $page     = $this->request->param('page', 1, 'intval');
        $pageSize = $this->request->param('limit', 10, 'intval');
        if ($keyword === '') {
            throw new ValidateException('keyword不能为空');
        }
        $options = [
            'category_id' => $this->request->param('category_id', 0, 'intval'),
            'tag_ids'     => $this->request->param('tag_ids'),
            'status'      => $this->request->param('status', 0, 'intval'),
            'order'       => $this->request->param('order', 'newest', 'trim'),
        ];
        $configService = app(ConfigService::class);
        $searchService = SearchServiceFactory::make($configService);
        $result = $searchService->searchNovels($keyword, $page, $pageSize, $options);
        if (!empty($keyword)) {
            SearchLogService::write( $keyword, $options);
        }
        return $this->ajaxReturn(200, '获取成功', [
            'list'  => $result['list'] ?? [],
            'count' => (int)($result['total'] ?? 0),
        ]);
    }
    public function hot_keywords()
    {
        $keywords = SearchLogService::getHotKeywords();
        return $this->ajaxReturn(200, '获取成功', $keywords);
    }
}
