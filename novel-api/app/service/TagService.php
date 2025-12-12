<?php
declare(strict_types=1);

namespace app\service;

use app\model\Tag as TagModel;

class TagService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new TagModel();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function info(int $id, string $field = '*'): array
    {
        $m = new TagModel();
        return $m->infoById($id, $field);
    }
}
