<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Novel as NovelModel;
use think\Request;
use app\service\StorageService;
use think\facade\Db;

class Novel extends BaseController
{
    /**
     * 小说列表
     *
     * 路由：`GET|POST /api/novels`
     * 鉴权：无需登录
     * 筛选：`category_id`、`tag_id`、`order`
     * 分页：`page`、`limit`（默认10，最大50）
     * 返回：列表与总数
     *
     * @param Request $request
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $categoryId = (int) $request->get('category_id', 0);
        $tagId      = (int) $request->get('tag_id', 0);
        $page       = max(1, (int) $request->get('page', 1));
        $pageSize   = min(50, max(1, (int) $request->get('limit', 10)));
        $order      = $request->get('order', 'newest');
        $result = \app\service\ContentService::listNovels([
            'category_id' => $categoryId ?: null,
            'tag_id'      => $tagId ?: null,
            'order'       => $order,
        ], $page, $pageSize);
        return api_response(200, '成功', $result['list'], (int)$result['total']);
    }

    /**
     * 小说详情（外部ID）
     *
     * 路由：`GET|POST /api/novels/:novelId`
     * 鉴权：无需登录
     * 参数：`:novelId` 使用 `novel_uuid`（兼容内部数值ID）
     * 返回：详情视图与章节预览
     *
     * @param string $novelId 外部小说ID（`novel_uuid`）
     * @return \think\Response
     */
    public function show(string $novelId)
    {
        $novelPk = NovelModel::where('novel_uuid', $novelId)->value('id');
        if (!$novelPk && ctype_digit($novelId)) {
            $novelPk = (int) $novelId;
        }
        if (!$novelPk) {
            return api_response(404, '小说不存在', [])->code(404);
        }
        $row = \app\service\ContentService::novelDetail($novelPk, 20);
        return api_response(200, '获取成功', $row);
    }

    
}
