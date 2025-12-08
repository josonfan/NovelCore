<?php
namespace app\admin\controller;

use app\admin\service\RbacService;
/**
 * 角色管理
 */
class Roles extends Backend
{
    /**
     * 获取角色列表
     */
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $field = 'id,name,description,created_at';
        $orderby = 'id desc';
        $m = new \app\admin\model\Roles();
        $list = $m->getList(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $list);
    }
    /**
     * 创建角色
     */
    public function create()
    {
        $postField = 'name,description';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $role = RbacService::createRole($data);
        return $this->ajaxReturn(200, '创建成功', $role);
    }
    /**
     * 获取角色详情
     */
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $m = new \app\admin\model\Roles();
        $info = $m->infoById($id, 'id,name,description,created_at');
        if (empty($info)) {
            return $this->ajaxReturn(404, '角色不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }
    /**
     * 为角色绑定权限
     */
    public function assignPermissions()
    {
        $postField = 'role_id,perm_ids';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $roleId = (int)($data['role_id'] ?? 0);
        $permIds = array_filter(array_map('intval', explode(',', (string)($data['perm_ids'] ?? ''))));
        RbacService::assignPermissions($roleId, $permIds);
        return $this->ajaxReturn(200, '绑定成功');
    }

    /**
     * 更新角色
     */
    public function update()
    {
        $postField = 'id,name,description';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        $ok = RbacService::updateRole($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }

    /**
     * 删除角色
     */
    public function delete()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $ok = RbacService::deleteRole($id);
        return $this->ajaxReturn(200, '删除成功', ['id' => $id, 'success' => $ok]);
    }

}
