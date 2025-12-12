<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\service\TagService;
use think\Request;

class Tag extends BaseController
{
    /**
     * 获取标签列表
     *
     * 路由：`GET|POST /api/tags`
     * 鉴权：无需登录
     * 分页：`page`（默认1）、`limit`（默认10）
     * 过滤：`type`（可选）
     * 返回：`{ code:200, msg:'成功', data:{ list:[], count:number } }`
     *
     * @param Request $request
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $page   = (int) $request->param('page', 1);
        $limit  = (int) $request->param('limit', 10);
        $type   = trim((string) $request->param('type', ''));
        $where  = [];
        if ($type !== '') {
            $where['type'] = ['=', $type];
        }
        $field   = 'id,name,slug,type,created_at';
        $orderby = 'id desc';
        $res = TagService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return api_response(200, '成功', $res);
    }

    /**
     * 获取标签详情
     *
     * 路由：`GET|POST /api/tags/:id`
     * 鉴权：无需登录
     * 参数：`id` 标签ID（内部自增）
     * 返回：`{ code:200, msg:'成功', data:{ id,name,slug,type,created_at } }`
     *
     * @param Request $request
     * @return \think\Response
     */
    public function info(Request $request)
    {
        $id = (int) $request->param('id', 0);
        $field = 'id,name,slug,type,created_at';
        $res = TagService::info($id, $field);
        return api_response(200, '成功', $res);
    }
}
