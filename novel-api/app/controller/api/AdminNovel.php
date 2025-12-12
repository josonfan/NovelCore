<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Novel;
use think\Request;

class AdminNovel extends BaseController
{
    /**
     * 小说统计列表（后台）
     *
     * 路由：`GET /api/admin/novels`
     * 鉴权：需 Admin Token
     * 排序：`order`（`like_count|fav_count|view_count`，默认 `view_count`）
     * 分页：`page`、`limit`（默认20，最大200）
     * 返回：列表与总数
     *
     * @param Request $request
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $orderField = $request->get('order', 'view_count');
        $allowed = ['like_count', 'fav_count', 'view_count'];
        if (!in_array($orderField, $allowed, true)) {
            $orderField = 'view_count';
        }
        $page    = max(1, (int) $request->get('page', 1));
        $perPage = min(200, max(1, (int) $request->get('limit', 20)));
        $paginator = Novel::order($orderField, 'desc')
            ->field(['id', 'novel_uuid', 'title', 'author_id', 'like_count', 'fav_count', 'view_count', 'created_at'])
            ->paginate(['list_rows' => $perPage, 'page' => $page]);
        return api_response(200, '成功', $paginator->items(), (int)$paginator->total());
    }
}
