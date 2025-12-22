<?php
namespace app\admin\controller;

use app\admin\service\SitesService;
use think\exception\ValidateException;

/**
 * 站点管理控制器
 * 提供站点列表、详情、创建、更新、删除、启停等接口
 */
class Sites extends Backend
{
    /**
     * 站点列表
     * @param int $page 页码（query: page）
     * @param int $limit 每页数量（query: limit）
     * @return \think\Response
     */
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $field = 'id,name,code,base_api_url,primary_domain,api_token,is_active,remark,created_at,updated_at';
        $orderby = 'id desc';
        $res = SitesService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }
    /**
     * 站点详情
     * @param int $id 站点ID（query: id）
     * @return \think\Response
     */
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,name,code,base_api_url,primary_domain,api_token,is_active,remark,created_at,updated_at';
        $info = SitesService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '站点不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }
    /**
     * 创建站点
     * @param array $data 提交数据（post: name,code,base_api_url,primary_domain,api_token,is_active,remark）
     * @return \think\Response
     */
    public function create()
    {
        $postField = 'name,code,base_api_url,primary_domain,api_token,is_active,remark';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $m = SitesService::create($data);
        $out = getArrayByFields($m->toArray(), 'id,name,code,base_api_url,primary_domain,is_active,remark,created_at,updated_at');
        return $this->ajaxReturn(200, '创建成功', $out);
    }
    /**
     * 更新站点（站点代号 code 不可修改）
     * @param array $data 提交数据（post: id,name,base_api_url,primary_domain,is_active,remark）
     * @return \think\Response
     */
    public function update()
    {
        $postField = 'id,name,base_api_url,primary_domain,is_active,remark';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        unset($data['id']);
        $ok = SitesService::update($id, $data);
        return $this->ajaxReturn(200, '更新成功', ['id' => $id, 'success' => $ok]);
    }
    /**
     * 删除站点
     * @param int $id 站点ID（query: id）
     * @return \think\Response
     */
    public function delete()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $ok = SitesService::delete($id);
        return $this->ajaxReturn(200, '删除成功', ['id' => $id, 'success' => $ok]);
    }
    /**
     * 启停站点
     * @param array $data 提交数据（post: id,is_active）
     * @return \think\Response
     */
    public function toggle()
    {
        $postField = 'id,is_active';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        $isActive = (int)($data['is_active'] ?? 1);
        $ok = SitesService::toggle($id, $isActive);
        return $this->ajaxReturn(200, '启停成功', ['id' => $id, 'success' => $ok]);
    }

    /**
     * 初始化
     * @param int $site_id 站点ID（post: site_id）
     * @return \think\Response
     */
    public function init()
    {
        $postField = 'site_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $siteId = (int)($data['site_id'] ?? 0);
        if ($siteId <= 0) {
            throw new ValidateException('参数错误：site_id');
        }
        $count = SitesService::initRecords($siteId);
        return $this->ajaxReturn(200, '初始化记录创建完成', ['site_id' => $siteId, 'created' => $count]);
    }
}
