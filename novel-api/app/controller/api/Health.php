<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;

class Health extends BaseController
{
    /**
     * 健康检查
     *
     * 路由：`GET /api/health`
     * 鉴权：无需登录
     * 返回：服务状态与数据库连通性摘要
     *
     * @return \think\Response
     */
    public function index()
    {
        $dbOk = false;
        try {
            $dbOk = \think\facade\Db::query("SELECT 1") ? true : false;
        } catch (\Throwable $e) {
            $dbOk = false;
        }
        $users = null;
        try {
            $users = \think\facade\Db::name('users')->count();
        } catch (\Throwable $e) {
            $users = null;
        }
        return api_response(200, 'OK', ['status' => 'ok', 'db' => $dbOk, 'users' => $users]);
    }
}
