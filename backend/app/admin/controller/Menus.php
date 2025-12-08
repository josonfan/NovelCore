<?php
namespace app\admin\controller;

use app\admin\service\MenuService;
use app\common\service\JwtService;

/**
 * 菜单管理控制器
 * 职责：提供菜单树、分页列表、详情、增删改与权限绑定接口
 * 中间件：AdminAuth + RbacAuth（路由层绑定）
 */
class Menus extends Backend
{
    /**
     * 获取当前管理员可见的菜单树
     * 返回根据权限过滤后的层级结构
     * @return \think\Response
     */
    public function index()
    {
        $uid = $this->request->uid;
        $tree = MenuService::treeForAdmin($uid);
        return $this->ajaxReturn(200, '成功', $tree);
    }
    /**
     * 菜单分页列表
     * 请求参数：page, limit
     * @return \think\Response
     */
    public function list()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');

        $where = [];
        $where['parent_id'] = $this->request->param('parent_id', 0, 'intval');
        $field = 'id,parent_id,name,code,path,route,icon,type,visible,is_active,sort_order,created_at,updated_at';
        $orderby = 'id desc';
        $res = MenuService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }
    /**
     * 菜单详情
     * 请求参数：id
     * @return \think\Response
     */
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,parent_id,name,code,path,route,icon,type,visible,is_active,sort_order,created_at,updated_at';
        $info = MenuService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '菜单不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }
    /**
     * 创建菜单
     * 请求字段：parent_id,name,code,path,route,icon,type,visible,is_active,sort_order
     * @return \think\Response
     */
    public function create()
    {
        $postField = 'parent_id,name,code,path,route,icon,type,visible,is_active,sort_order';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $m = MenuService::create($data);
        return $this->ajaxReturn(200, '创建成功', $m);
    }
    /**
     * 更新菜单
     * 请求字段：id,parent_id,name,code,path,route,icon,type,visible,is_active,sort_order
     * @return \think\Response
     */
    public function update()
    {
        $postField = 'id,parent_id,name,code,path,route,icon,type,visible,is_active,sort_order';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        $ok = MenuService::update($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }
    /**
     * 删除菜单
     * 请求参数：id
     * @return \think\Response
     */
    public function delete()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $ok = MenuService::delete($id);
        return $this->ajaxReturn(200, '删除成功', ['id' => $id, 'success' => $ok]);
    }
    /**
     * 绑定菜单权限
     * 请求字段：menu_id, perm_ids（逗号分隔）
     * @return \think\Response
     */
    public function bindPermissions()
    {
        $postField = 'menu_id,perm_ids';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $menuId = (int)($data['menu_id'] ?? 0);
        $permIds = array_filter(array_map('intval', explode(',', (string)($data['perm_ids'] ?? ''))));
        $ok = MenuService::bindPermissions($menuId, $permIds);
        return $this->ajaxReturn(200, '绑定成功', ['success' => $ok]);
    }
    
    /**
     * 上级菜单选项（最多3级）
     * 仅返回启用的菜单，结构包含 id/name/parent_id/children
     * @return \think\Response
     */
    public function options()
    {
        $res = MenuService::options(3);
        return $this->ajaxReturn(200, '成功', $res);
    }
}
