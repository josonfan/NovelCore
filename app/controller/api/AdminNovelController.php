<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Novel;
use think\Request;

/**
 * 后台小说统计查询接口，按点赞/收藏/浏览排序。
 */
class AdminNovelController extends BaseController
{
    /**
     * 小说列表（按统计字段排序），仅后台调用。
     */
    public function index(Request $request)
    {
        $orderField = $request->get('order', 'view_count');
        $allowed = ['like_count', 'fav_count', 'view_count'];
        if (!in_array($orderField, $allowed, true)) {
            $orderField = 'view_count';
        }

        $page    = max(1, (int) $request->get('page', 1));
        $perPage = min(200, max(1, (int) $request->get('per_page', 20)));

        $paginator = Novel::order($orderField, 'desc')
            ->field(['id', 'novel_uuid', 'title', 'author_id', 'like_count', 'fav_count', 'view_count', 'created_at'])
            ->paginate([
                'list_rows' => $perPage,
                'page'      => $page,
            ]);

        return json_success([
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->listRows(),
            'total'        => $paginator->total(),
            'data'         => $paginator->items(),
        ]);
    }
}
