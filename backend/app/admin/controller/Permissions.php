<?php
namespace app\admin\controller;

use app\admin\service\RbacService;
/**
 * 权限管理
 */
class Permissions extends Backend
{
    /**
     * 创建权限
     *
     * @return array
     */
    public function create()
    {
        $postField = 'name,resource,action,field';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $perm = RbacService::createPermission($data);
        return $this->ajaxReturn(200, '创建成功', $perm);
    }

    /**
     * 权限列表
     *
     * @return array
     */
    public function index()
    {
        $list = RbacService::permissions();
        return $this->ajaxReturn(200, '成功', $list);
    }

    /**
     * 权限详情
     *
     * @return array
     */
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $m = new \app\admin\model\Permissions();
        $info = $m->infoById($id, 'id,name,resource,action,field,created_at');
        if (empty($info)) {
            return $this->ajaxReturn(404, '权限不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }

    /**
     * 更新权限
     *
     * @return array
     */
    public function update()
    {
        $postField = 'id,name,resource,action,field';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        $ok = RbacService::updatePermission($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }

    /**
     * 删除权限
     *
     * @return array
     */
    public function delete()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $ok = RbacService::deletePermission($id);
        return $this->ajaxReturn(200, '删除成功', ['id' => $id, 'success' => $ok]);
    }
}
