<?php
declare(strict_types=1);

namespace app\service;

use app\model\Category as CategoryModel;

class CategoryService
{
    
    public static function list(array $where = [], string $field = '*', string $orderby = 'sort_order desc, id desc', int $limit = 10, int $page = 1): array
    {
        $m = new CategoryModel();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }
    public static function info(int $id, string $field = '*'): array
    {
        $m = new CategoryModel();
        return $m->infoById($id, $field);
    }
}