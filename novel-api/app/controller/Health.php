<?php
declare(strict_types=1);

namespace app\controller;

use app\service\HealthService;
use think\facade\Lang;
class Health extends Common
{
    /**
     * 健康检查
     * 路由：GET /api/Health/index
     * 鉴权：无需登录
     * 入参：无
     * 返回：data { ts }
     */
    public function index()
    {
        $queueLengths = HealthService::getQueue();
        $dbStatus = HealthService::getDbStatus();
        $cacheStatus = HealthService::getCacheStatus();
        return $this->ajaxReturn(200, 'OK', [
            'ts' => time(),
            'lang' => Lang::getLangSet(),
            'queue'=>$queueLengths,
            'db'=>$dbStatus,
            'cache'=>$cacheStatus
        ]);
    }
}
