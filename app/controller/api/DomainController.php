<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\service\DomainService;
use think\Request;

/**
 * 域名池接口：前端可据此测速选择域名。
 */
class DomainController extends BaseController
{
    public function index(Request $request, DomainService $domainService)
    {
        $type = (string) $request->get('type', 'api');
        $domains = $domainService->getActiveDomainsByType($type);

        return json_success([
            'type'    => $type,
            'domains' => $domains,
        ], '获取成功');
    }
}
