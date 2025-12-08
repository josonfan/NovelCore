<?php
namespace app\admin\service;

use app\admin\model\Categories;
use think\exception\ValidateException;

class CategoriesService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'sort_order desc, id desc', int $limit = 10, int $page = 1): array
    {
        $m = new Categories();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function detail(int $id, string $field = '*')
    {
        $m = new Categories();
        return $m->infoById($id, $field);
    }

    public static function create(array $data)
    {
        try {
            validate(\app\admin\validate\Category::class)->scene('create')->check($data);
            $m = new Categories($data);
            $m->created_at = time();
            $m->writeById((int)$m['id'], $m->toArray());
            return $m;
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function update(int $id, array $data): bool
    {
        try {
            validate(\app\admin\validate\Category::class)->scene('update')->check($data);
            $m = new Categories();
            return $m->writeById($id, $data);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function delete(int $id): bool
    {
        $m = new Categories();
        $pk = $m->getPk();
        $ok = (bool)$m->where($pk, $id)->delete();
        \think\facade\Cache::delete(env('DATABASE.PREFIX', 'blad_') . 'categories_' . $id);
        return $ok;
    }

    public static function toggle(int $id, int $isActive): bool
    {
        $m = new Categories();
        return $m->writeById($id, ['is_active' => $isActive]);
    }
}
