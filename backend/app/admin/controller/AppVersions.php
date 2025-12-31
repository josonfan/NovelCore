<?php

namespace app\admin\controller;

use app\admin\service\AppVersionsService;

class AppVersions extends Backend
{
    /**
     * 列表
     */
    public function index()
    {
        $params = $this->request->only(['site_id', 'platform', 'page', 'limit']);
        $data = AppVersionsService::list($params);
        return $this->ajaxReturn(200, 'success', $data);
    }

    /**
     * 详情
     */
    public function detail()
    {
        $id = $this->request->param('id/d', 0);
        $data = AppVersionsService::detail($id);
        return $this->ajaxReturn(200, 'success', $data);
    }

    /**
     * 保存
     */
    public function save()
    {
        $params = $this->request->only(['id', 'site_id', 'platform', 'version_code', 'version_name', 'content', 'download_url', 'is_force', 'status'], 'post');
        AppVersionsService::save($params);
        return $this->ajaxReturn(200, '操作成功');
    }

    /**
     * 删除
     */
    public function delete()
    {
        $id = $this->request->param('id/d', 0);
        AppVersionsService::delete($id);
        return $this->ajaxReturn(200, '删除成功');
    }
    /**
     * 启停
     */
    public function toggle()
    {
        $postField = 'id,status';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $id = (int)($data['id'] ?? 0);
        $status = (int)($data['status'] ?? 1);
        $ok = AppVersionsService::toggle($id, $status);
        return $this->ajaxReturn(200, '启停成功', ['id' => $id, 'success' => $ok]);
    }
}
