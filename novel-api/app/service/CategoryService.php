<?php
declare(strict_types=1);

namespace app\service;

use app\model\Category as CategoryModel;

class CategoryService
{
    
    /**
     * 获取分类列表（统一模型列表方法）
     * - 列表查询统一使用模型的 getList($where,$field,$orderby,$limit,$page)
     *
     * @param array $where 查询条件（建议使用 formatWhere 转换）
     * @param string $field 列表视图字段
     * @param string $orderby 排序，如 'sort_order desc, id desc'
     * @param int $limit 每页数量
     * @param int $page 页码
     * @return array { list: array, count: int }
     */
    public static function list(array $where = [], string $field = '*', string $orderby = 'sort_order desc, id desc', int $limit = 10, int $page = 1): array
    {
        $m = new CategoryModel();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }
    /**
     * 获取分类详情（统一模型详情方法）
     * - 详情查询统一使用模型的 infoById($id,$field)
     *
     * @param int $id 主键ID
     * @param string $field 视图字段
     * @return array 详情视图数据
     */
    public static function info(int $id, string $field = '*'): array
    {
        $m = new CategoryModel();
        return $m->infoById($id, $field);
    }
}
