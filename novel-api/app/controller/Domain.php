<?php
declare(strict_types=1);

namespace app\controller;

use app\service\DomainService;

class Domain extends Common
{
    /**
     * 域名池
     * 路由：POST /api/Domain/index
     * 鉴权：无需登录
     * 入参：type（api|image|share）
     * 返回：data { list: [{domain,type}], count }
     */
    public function index()
    {
        $type = $this->request->param('type', 'api', 'trim');
        $domainService = app(DomainService::class);
        $domains = $domainService->getActiveDomainsByType($type);
        $list = array_map(fn($d) => ['domain' => $d, 'type' => $type], (array)$domains);
        return $this->ajaxReturn(200, '获取成功', [
            'list'  => $list,
            'count' => count($list),
        ]);
    }
}
