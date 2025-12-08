<?php
namespace app\admin\controller;

use app\admin\service\AiConfigService;

class AiConfig extends Backend
{
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $siteId = $this->request->param('site_id', null);
        if ($siteId !== null && $siteId !== '') {
            $where['site_id'] = (int)$siteId;
        }
        $field = 'id,site_id,provider,api_key,api_base_url,model,is_active,updated_at';
        $orderby = 'id desc';
        $res = AiConfigService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,site_id,provider,api_key,api_base_url,model,is_active,updated_at';
        $info = AiConfigService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }
    public function detailBySite()
    {
        $siteId = (int)$this->request->param('site_id', 0);
        $info = AiConfigService::getBySiteId($siteId);
        if (empty($info)) {
            return $this->ajaxReturn(404, '不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }

    public function save()
    {
        $postField = 'site_id,provider,api_key,api_base_url,model,is_active';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = AiConfigService::save($data);
        return $this->ajaxReturn(200, '保存成功', $res);
    }
}
