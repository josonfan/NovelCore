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
            $data['field'] = (string)($data['field'] ?? '');
            $fieldVal = $data['field'] ?? null;
            $exists = Permissions::where('resource', (string)$data['resource'])
                ->where('action', (string)$data['action'])
                ->where(function($q) use ($fieldVal){
                    if ($fieldVal === null || $fieldVal === '') {
                        $q->whereNull('field')->whereOr('field','');
                    } else {
                        $q->where('field', (string)$fieldVal);
                    }
                })
                ->find();
            if ($exists) {
                return $exists;
            }
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
     * 更新角色
     */
    public static function updateRole(int $id, array $data): bool
    {
        try{
            // name 格式校验与唯一性手工校验（如有传入）
            if (isset($data['name'])) {
                $name = (string)$data['name'];
                if ($name === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $name)) {
                    throw new ValidateException('角色名称只允许字母数字下划线和破折号');
                }
                $exists = Roles::where('name', $name)->where('id', '<>', $id)->value('id');
                if ($exists) {
                    throw new ValidateException('角色名称已存在');
                }
            }
            $m = new Roles();
            return $m->writeById($id, $data);
        }catch(ValidateException $e){
            throw new ValidateException($e->getError());
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 删除角色
     */
    public static function deleteRole(int $id): bool
    {
        $m = new Roles();
        $ok = $m->deleteById($id);
        if ($ok) {
            $rpRows = RolePermission::where('role_id', $id)->field('id')->select()->toArray();
            foreach ($rpRows as $r) {
                (new RolePermission())->deleteById((int)$r['id']);
            }
            $arRows = AdminRole::where('role_id', $id)->field('id')->select()->toArray();
            foreach ($arRows as $r) {
                (new AdminRole())->deleteById((int)$r['id']);
            }
        }
        return $ok;
    }

    /**
     * 更新权限
     */
    public static function updatePermission(int $id, array $data): bool
    {
        try{
            $v = validate(\app\admin\validate\Permission::class)->scene('create');
            $v->remove('resource','require');
            $v->remove('action','require');
            $v->check($data);
            $m = new Permissions();
            return $m->writeById($id, $data);
        }catch(ValidateException $e){
            throw new ValidateException($e->getError());
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 删除权限
     */
    public static function deletePermission(int $id): bool
    {
        $m = new Permissions();
        $ok = $m->deleteById($id);
        if ($ok) {
            $rpRows = RolePermission::where('perm_id', $id)->field('id')->select()->toArray();
            foreach ($rpRows as $r) {
                (new RolePermission())->deleteById((int)$r['id']);
            }
        }
        return $ok;
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
            $rows = AdminRole::where('admin_id', $adminId)->field('id')->select()->toArray();
            foreach ($rows as $r) {
                (new AdminRole())->deleteById((int)$r['id']);
            }
            foreach ($roleIds as $rid) {
                if (!$rid) continue;
                (new AdminRole())->writeById(0, ['admin_id' => $adminId, 'role_id' => (int)$rid]);
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
        $rows = RolePermission::where('role_id', $roleId)->field('id')->select()->toArray();
        foreach ($rows as $r) {
            (new RolePermission())->deleteById((int)$r['id']);
        }
        foreach ($permIds as $pid) {
            if (!$pid) continue;
            (new RolePermission())->writeById(0, ['role_id' => $roleId, 'perm_id' => (int)$pid]);
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
