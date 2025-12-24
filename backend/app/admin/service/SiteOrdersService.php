<?php
namespace app\admin\service;

use app\admin\model\SiteOrders;
use think\exception\ValidateException;

class SiteOrdersService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new SiteOrders();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function detail(int $id, string $field = '*')
    {
        $m = new SiteOrders();
        $info = $m->infoById($id, $field);
        if (empty($info)) {
            throw new ValidateException('订单不存在');
        }
        return $info;
    }

    public static function update(int $id, array $data): bool
    {
        try {
            validate(\app\admin\validate\SiteOrders::class)->scene('update')->check(array_merge($data, ['id' => $id]));
            $info = self::detail($id);
            foreach ($data as $key => $value) {
                $info[$key] = $value;
            }
            $m = new SiteOrders();
            return $m->writeById($id, $info);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new ValidateException($e->getMessage());
        }
    }
}

