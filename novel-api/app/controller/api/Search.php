<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\service\ConfigService;
use app\service\SearchLogService;
use app\service\search\SearchServiceFactory;
use think\Request;

class Search extends BaseController
{
    /**
     * 搜索小说
     *
     * 路由：`GET|POST /api/search`
     * 鉴权：无需登录
     * 参数：`keyword` 关键词；可选 `category_id`、`tag_ids`、`status`、`order`
     * 分页：`page`、`limit`（默认10，最大50）
     * 返回：列表与总数
     *
     * @param Request $request
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $keyword  = trim((string) $request->param('keyword', ''));
        $page     = max(1, (int) $request->param('page', 1));
        $pageSize = min(50, max(1, (int) $request->param('limit', 10)));
        if ($keyword === '') {
            return api_response(400, 'keyword不能为空', []);
        }
        $options = [
            'category_id' => $request->get('category_id'),
            'tag_ids'     => $request->get('tag_ids'),
            'status'      => $request->get('status'),
            'order'       => $request->get('order'),
        ];
        $configService = app(ConfigService::class);
        $searchService = SearchServiceFactory::make($configService);
        $result = $searchService->searchNovels($keyword, $page, $pageSize, $options);
        SearchLogService::write(($request->user->id ?? null) ?? null, $keyword, [
            'page'  => $page,
            'limit' => $pageSize,
        ]);
        return api_response(200, '成功', $result['list'] ?? [], (int)($result['total'] ?? 0));
    }

}
