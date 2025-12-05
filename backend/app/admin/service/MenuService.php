<?php
namespace app\admin\service;

use think\facade\Db;
use think\exception\ValidateException;
use app\admin\model\Menus;
use app\admin\model\MenuPermission;

class MenuService
{
    public static function treeForAdmin(int $adminId): ?array
    {
        try {
            $permIds = Db::name('admin_role')->alias('ar')
                ->join('role_permission rp', 'ar.role_id = rp.role_id')
                ->field('rp.perm_id')
                ->where('ar.admin_id', $adminId)
                ->select()
                ->column('perm_id');

            $menus = Menus::where('is_active', 1)
                ->order('sort_order desc, id asc')
                ->select()
                ->toArray();

            $links = MenuPermission::select()->toArray();
        } catch (\Throwable $e) {
            return [];
        }

        $permMap = [];
        foreach ($links as $ln) {
            $permMap[$ln['menu_id']][] = (int)$ln['perm_id'];
        }

        $allowed = [];
        $byParent = [];
        foreach ($menus as $m) {
            $byParent[(int)$m['parent_id']][] = $m;
            $required = $permMap[$m['id']] ?? [];
            $has = empty($required) ? (int)$m['visible'] === 1 : count(array_intersect($permIds, $required)) > 0;
            if ($has) {
                $allowed[$m['id']] = $m;
            }
        }

        $tree = self::buildTree(0, $byParent, $allowed);
        return $tree;
    }

    private static function buildTree(int $parentId, array $byParent, array $allowed): array
    {
        $children = $byParent[$parentId] ?? [];
        $out = [];
        foreach ($children as $m) {
            $id = (int)$m['id'];
            $node = $m;
            $node['children'] = self::buildTree($id, $byParent, $allowed);
            if (isset($allowed[$id]) || !empty($node['children'])) {
                $out[] = $node;
            }
        }
        return $out;
    }
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new Menus();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function detail(int $id, string $field = '*')
    {
        $m = new Menus();
        return $m->infoById($id, $field);
    }

    public static function create(array $data)
    {
        try {
            validate(\app\admin\validate\Menu::class)->scene('create')->check($data);
            $data['created_at'] = time();
            $data['updated_at'] = time();
            $m = new Menus($data);
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
           
            validate(\app\admin\validate\Menu::class)->scene('update')->check($data);
            $m = new Menus();
            return $m->writeById($id, $data);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function delete(int $id): bool
    {
        $m = new Menus();
        $pk = $m->getPk();
        $ok = (bool)$m->where($pk, $id)->delete();
        $m->setCacheData($m->getCacheKey($id), null);
        MenuPermission::where('menu_id', $id)->delete();
        return $ok;
    }

    public static function bindPermissions(int $menuId, array $permIds): bool
    {
        MenuPermission::where('menu_id', $menuId)->delete();
        foreach ($permIds as $pid) {
            if (!$pid) continue;
            $mp = new MenuPermission(['menu_id' => $menuId, 'perm_id' => (int)$pid]);
            $mp->save();
        }
        return true;
    }
}
