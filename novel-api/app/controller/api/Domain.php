<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\service\DomainService;
use think\Request;

class Domain extends BaseController
{
    /**
     * 获取域名池
     *
     * 路由：`GET|POST /api/domains`
     * 鉴权：无需登录
     * 参数：`type` 域名类型（`api|image|share`），默认 `api`
     * 返回：按优先级排序的启用域名列表
     *
     * @param Request $request
     * @param DomainService $domainService
     * @return \think\Response
     */
    public function index(Request $request, DomainService $domainService)
    {
        $type = (string) $request->param('type', 'api');
        $domains = $domainService->getActiveDomainsByType($type);
        $list = array_map(fn($d) => ['domain' => $d, 'type' => $type], (array)$domains);
        return api_response(200, '成功', $list, count($list));
    }
}
