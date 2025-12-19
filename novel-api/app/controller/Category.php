<?php
declare(strict_types=1);

namespace app\controller;

use app\service\CategoryService;

class Category extends Common
{
    /**
     * 分类列表
     * 路由：POST /api/Category/index
     * 鉴权：无需登录
     * 入参：page, limit
     * 返回：data { list, count }
     */
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        // 去掉分页参数校验
        $where = [];
        $field = 'id,name,slug,sort_order,is_active,seo_title,seo_keywords,seo_description';
        $orderby = 'sort_order desc, id desc';
        $res = CategoryService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    /**
     * 分类详情
     * 路由：POST /api/Category/info
     * 鉴权：无需登录
     * 入参：id
     * 返回：data 对象（主表字段）
     */
    public function info()
    {
        $id = $this->request->param('id', 0, 'intval');
        $field = 'id,name,slug,sort_order,is_active,seo_title,seo_keywords,seo_description';
        $res = CategoryService::info((int)$id, $field);
        return $this->ajaxReturn(200, '获取成功', $res);
    }
}
