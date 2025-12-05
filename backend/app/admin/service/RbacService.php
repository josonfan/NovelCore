<?php
namespace app\admin\service;

use think\facade\Db;
use app\admin\model\Roles;
use app\admin\model\Permissions;
use app\admin\model\RolePermission;
use app\admin\model\AdminRole;
use think\exception\ValidateException;

class RbacService
{
    public static function createRole(array $data)
    {
        validate(\app\admin\validate\Role::class)->scene('create')->check($data);
        $role = new Roles($data);
        $role->save();
        return $role;
    }

    public static function createPermission(array $data)
    {
        validate(\app\admin\validate\Permission::class)->scene('create')->check($data);
        $perm = new Permissions($data);
        $perm->save();
        return $perm;
    }

    public static function assignRoles(int $adminId, array $roleIds)
    {
        AdminRole::where('admin_id', $adminId)->delete();
        foreach ($roleIds as $rid) {
            if (!$rid) continue;
            $m = new AdminRole(['admin_id' => $adminId, 'role_id' => (int)$rid]);
            $m->save();
        }
        return true;
    }

    public static function assignPermissions(int $roleId, array $permIds)
    {
        RolePermission::where('role_id', $roleId)->delete();
        foreach ($permIds as $pid) {
            if (!$pid) continue;
            $m = new RolePermission(['role_id' => $roleId, 'perm_id' => (int)$pid]);
            $m->save();
        }
        return true;
    }

    public static function getAdminPermissions(int $adminId): array
    {
        $list = Db::name('admin_role')->alias('ar')
            ->join('role_permission rp', 'ar.role_id = rp.role_id')
            ->join('permissions p', 'rp.perm_id = p.id')
            ->field('p.resource,p.action,p.field')
            ->where('ar.admin_id', $adminId)
            ->select()
            ->toArray();
        return $list;
    }

    public static function check(int $adminId, string $resource, string $action, ?string $field = null): bool
    {
        $query = Db::name('admin_role')->alias('ar')
            ->join('role_permission rp', 'ar.role_id = rp.role_id')
            ->join('permissions p', 'rp.perm_id = p.id')
            ->where('ar.admin_id', $adminId)
            ->where('p.resource', $resource)
            ->where('p.action', $action);
        if ($field !== null && $field !== '') {
            $query = $query->where('p.field', $field);
        }
        $exists = $query->limit(1)->find();
        return !empty($exists);
    }

    public static function roles(): array
    {
        return Roles::order('id desc')->select()->toArray();
    }

    public static function permissions(): array
    {
        return Permissions::order('id desc')->select()->toArray();
    }
}
