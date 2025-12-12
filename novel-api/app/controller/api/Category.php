<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Category as CategoryModel;
use app\service\CategoryService;

class Category extends BaseController
{
    /**
     * 获取分类列表
     *
     * 路由：`GET|POST /api/categories`
     * 鉴权：无需登录
     * 分页：`page`（默认1）、`limit`（默认10）
     * 返回：`{ code:200, msg:'成功', data:{ list:[], count:number } }`
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $field = 'id,name,slug,sort_order,is_active,seo_title,seo_keywords,seo_description';
        $orderby = 'sort_order desc, id desc';
        $res = CategoryService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return api_response(200, '成功', $res);
    }
    /**
     * 获取分类详情
     *
     * 路由：`GET|POST /api/categories/:id`
     * 鉴权：无需登录
     * 参数：`id` 分类ID（内部自增）
     * 返回：`{ code:200, msg:'成功', data:{...} }`
     *
     * @return \think\Response
     */
    public function info()
    {
        $id = $this->request->param('id', 0, 'intval');
        $field = 'id,name,slug,sort_order,is_active,seo_title,seo_keywords,seo_description';
        $res = CategoryService::info($id, $field);
        return api_response(200, '成功', $res);
    }
}
