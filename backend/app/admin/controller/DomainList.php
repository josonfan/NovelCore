<?php
namespace app\admin\controller;

use app\admin\service\DomainListService;

/**
 * 域名管理控制器
 * 提供域名列表、详情、创建、更新、删除等接口
 */
class DomainList extends Backend
{
    /**
     * 域名列表
     * @param int $page 页码（query: page）
     * @param int $limit 每页数量（query: limit）
     * @return \think\Response
     */
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $field = 'id,site_id,domain,type,priority,is_active,remark,created_at,updated_at';
        $orderby = 'id desc';
        $res = DomainListService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }
    /**
     * 域名详情
     * @param int $id 域名ID（query: id）
     * @return \think\Response
     */
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,site_id,domain,type,priority,is_active,remark,created_at,updated_at';
        $info = DomainListService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '域名不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }
    /**
     * 创建域名
     * @param array $data 提交数据（post: site_id,domain,type,priority,is_active,remark）
     * @return \think\Response
     */
    public function create()
    {
        $postField = 'site_id,domain,type,priority,is_active,remark';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $m = DomainListService::create($data);
        $out = getArrayByFields($m->toArray(), 'id,site_id,domain,type,priority,is_active,remark,created_at,updated_at');
        return $this->ajaxReturn(200, '创建成功', $out);
    }
    /**
     * 更新域名
     * @param array $data 提交数据（post: id,site_id,domain,type,priority,is_active,remark）
     * @return \think\Response
     */
    public function update()
    {
        $postField = 'id,site_id,domain,type,priority,is_active,remark';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        $ok = DomainListService::update($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }
    /**
     * 删除域名
     * @param int $id 域名ID（query: id）
     * @return \think\Response
     */
    public function delete()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $ok = DomainListService::delete($id);
        return $this->ajaxReturn(200, '删除成功', ['id' => $id, 'success' => $ok]);
    }
}
