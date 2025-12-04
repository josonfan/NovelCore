<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\UserSearchLog;
use app\service\ConfigService;
use app\service\search\SearchServiceFactory;
use think\Request;

/**
 * 搜索接口：可切换 ES / MySQL，实现对前端透明。
 */
class SearchController extends BaseController
{
    /**
     * GET /api/search
     */
    public function index(Request $request)
    {
        $keyword  = trim((string) $request->get('keyword', ''));
        $page     = max(1, (int) $request->get('page', 1));
        $pageSize = min(50, max(1, (int) $request->get('page_size', 10)));

        if ($keyword === '') {
            return json_error('keyword不能为空');
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

        // 搜索日志埋点
        $this->writeSearchLog($request, $keyword, [
            'page'      => $page,
            'page_size' => $pageSize,
        ]);

        return json_success([
            'keyword'   => $keyword,
            'total'     => $result['total'] ?? 0,
            'page'      => $page,
            'page_size' => $pageSize,
            'results'   => $result['list'] ?? [],
        ], 'OK');
    }

    /**
     * 写入搜索日志，失败忽略。
     */
    protected function writeSearchLog(Request $request, string $keyword, array $filters): void
    {
        try {
            $user = $request->user ?? null;
            $userId = $user->id ?? null;
            UserSearchLog::create([
                'user_id'     => $userId,
                'keyword'     => $keyword,
                'filters_json'=> $filters,
            ]);
        } catch (\Throwable $e) {
            // 忽略日志失败
        }
    }
}
