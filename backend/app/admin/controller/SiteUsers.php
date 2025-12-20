<?php
namespace app\admin\controller;

use app\admin\service\SiteUsersService;

class SiteUsers extends Backend
{
    /**
     * 站点用户列表
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param int $site_id 站点ID
     * @param string $username 用户名
     * @param int $status 状态
     */
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        
        $siteId = $this->request->param('site_id');
        if ($siteId !== null && $siteId !== '') {
            $where[] = ['site_id', '=', (int)$siteId];
        }
        
        $username = $this->request->param('username');
        if ($username) {
            $where[] = ['username', 'like', "%{$username}%"];
        }
        
        $status = $this->request->param('status');
        if ($status !== null && $status !== '') {
            $where[] = ['status', '=', (int)$status];
        }

        $field = 'id,site_id,user_id,username,nickname,avatar,status,device,email,client_version,vip_expire,created_at,updated_at,last_synced_at';
        $orderby = 'id desc';
        
        $res = SiteUsersService::list($where, $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }

    /**
     * 站点用户详情
     * @param int $id ID
     */
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,site_id,user_id,username,nickname,avatar,status,device,email,client_version,vip_expire,created_at,updated_at,last_synced_at';
        $info = SiteUsersService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '用户不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }

    /**
     * 设置状态
     * @param int $id ID
     * @param int $status 状态(0/1)
     */
    public function setStatus()
    {
        $id = (int)$this->request->param('id');
        $status = (int)$this->request->param('status');
        $ok = SiteUsersService::setStatus($id, $status);
        return $this->ajaxReturn(200, '设置成功', ['success' => $ok]);
    }
}
