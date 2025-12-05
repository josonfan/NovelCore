<?php
namespace app\admin\service;

use think\facade\Db;
use app\admin\model\Menus;
use app\admin\model\MenuPermission;

class MenuService
{
    public static function treeForAdmin(int $adminId): array
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
}

