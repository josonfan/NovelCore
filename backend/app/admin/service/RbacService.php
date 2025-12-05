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
    /**
     * 创建角色
     * @param array $data 角色数据
     * @return Roles
     */
    public static function createRole(array $data)
    {
        try{
            validate(\app\admin\validate\Role::class)->scene('create')->check($data);
            $role = new Roles($data);
            $role->created_at = time();
            $role->writeById((int)$role['id'], $role->toArray());
            return $role;
        }catch(ValidateException $e){
            throw new ValidateException($e->getError());
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 创建权限
     * @param array $data 权限数据
     * @return Permissions
     */
    public static function createPermission(array $data)
    {
        try{
            validate(\app\admin\validate\Permission::class)->scene('create')->check($data);
            $perm = new Permissions($data);
            $perm->created_at = time();
            $perm->writeById((int)$perm['id'], $perm->toArray());
            return $perm;
        }catch(ValidateException $e){
            throw new ValidateException($e->getError());
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
    /**
     * 为管理员分配角色
     * @param int $adminId 管理员ID
     * @param array $roleIds 角色ID数组
     * @return bool
     */
    public static function assignRoles(int $adminId, array $roleIds)
    {
        try{
            if ($adminId === 1) {
                throw new ValidateException('超级管理员不可变更角色');
            }
            
            validate(\app\admin\validate\AdminRole::class)->scene('assign')->check(['admin_id' => $adminId, 'role_ids' => $roleIds]);
            AdminRole::where('admin_id', $adminId)->delete();
            foreach ($roleIds as $rid) {
                if (!$rid) continue;
                $m = new AdminRole(['admin_id' => $adminId, 'role_id' => (int)$rid]);
                $m->save();
            }
            return true;
        }catch(ValidateException $e){
            throw new ValidateException($e->getError());
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
    /**
     * 为角色分配权限
     * @param int $roleId 角色ID
     * @param array $permIds 权限ID数组
     * @return bool
     */
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
    /**
     * 获取管理员权限
     * @param int $adminId 管理员ID
     * @return array
     */
    public static function getAdminPermissions(int $adminId): array
    {
        $roleIds = AdminRole::where('admin_id', $adminId)->column('role_id');
        if (empty($roleIds)) return [];
        $permIds = [];
        foreach ($roleIds as $rid) {
            $ids = RolePermission::where('role_id', (int)$rid)->column('perm_id');
            if (!empty($ids)) {
                $permIds = array_merge($permIds, $ids);
            }
        }
        $permIds = array_values(array_unique(array_map('intval', $permIds)));
        if (empty($permIds)) return [];
        $permModel = new Permissions();
        $out = [];
        foreach ($permIds as $pid) {
            $p = $permModel->infoById((int)$pid, 'resource,action,field');
            if (!empty($p)) {
                $out[] = $p;
            }
        }
        return $out;
    }
    /**
     * 检查管理员是否有指定权限
     * @param int $adminId 管理员ID
     * @param string $resource 资源名称
     * @param string $action 操作名称
     * @param ?string $field 字段名称
     * @return bool
     */
    public static function check(int $adminId, string $resource, string $action, ?string $field = null): bool
    {
        $perms = self::getAdminPermissions($adminId);
        foreach ($perms as $p) {
            if (($p['resource'] ?? '') === $resource && ($p['action'] ?? '') === $action) {
                if ($field === null || $field === '' || ($p['field'] ?? '') === $field) {
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * 获取所有角色
     * @return array
     */
    public static function roles(): array
    {
        $m = new Roles();
        $res = $m->getList([], 'id,name,description,created_at', 'id desc', 10, 1);
        return $res['list'] ?? [];
    }
    /**
     * 获取所有权限
     * @return array
     */
    public static function permissions(): array
    {
        $m = new Permissions();
        $res = $m->getList([], 'id,name,resource,action,field', 'id desc', 10, 1);
        return $res['list'] ?? [];
    }
}
