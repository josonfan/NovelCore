<?php
declare(strict_types=1);

namespace app\service;

use app\model\Vip as VipModel;

class VipService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'sort desc, id desc', int $limit = 10, int $page = 1): array
    {
        $m = new VipModel();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function info(int $id, string $field = '*'): array
    {
        $m = new VipModel();
        return $m->infoById($id, $field);
    }
}
