<?php
declare(strict_types=1);

namespace app\middleware;

use think\middleware\Throttle;
use think\Request;
use think\Response;

/**
 * 通用API频率限制中间件
 * 支持通过路由参数配置 visit_rate 和 key_field
 */
class ApiThrottle extends Throttle
{
    /**
     * 处理请求
     * @param Request $request
     * @param \Closure $next
     * @param array $params 路由参数，如 ['visit_rate' => '1/m', 'key_field' => 'email']
     * @return Response
     */
    public function handle(Request $request, \Closure $next, array $params = []): Response
    {
        // 支持通过 key_field 指定请求参数作为限流 Key
        if (isset($params['key_field'])) {
            $field = $params['key_field'];
            $params['key'] = function ($throttle, $request) use ($field) {
                return $request->param($field) ?: $request->ip();
            };
            // 移除 key_field 避免污染 config
            unset($params['key_field']);
        }

        return parent::handle($request, $next, $params);
    }
}
