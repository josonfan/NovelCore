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
        // 1. 放行 OPTIONS 预检请求
        if ($request->method(true) === 'OPTIONS') {
            return $next($request);
        }

        // 2. 构造限流 Key 生成策略
        // 优先级：指定 key_field > 已登录用户ID > IP
        $keyField = $params['key_field'] ?? null;
        unset($params['key_field']); // 移除自定义参数，避免影响父类

        $params['key'] = function ($throttle, $request) use ($keyField) {
            $suffix = '';
            
            if ($keyField && $val = $request->param($keyField)) {
                // 策略 A: 指定字段 (如 email, username)
                $suffix = 'param:' . $val;
            } elseif (isset($request->user_id) && $request->user_id > 0) {
                // 策略 B: 已登录用户 (基于 user_id)
                $suffix = 'user:' . $request->user_id;
            } else {
                // 策略 C: 游客 (基于 IP)
                $suffix = 'ip:' . $request->ip();
            }

            // 组合 Key: 路由地址 + 身份标识 + 中间件隔离后缀
            // 加上 :api_throttle 是为了与全局 Throttle 中间件隔离，避免共享计数器
            return md5($request->url() . ':' . $suffix . ':api_throttle');
        };
        
        return parent::handle($request, $next, $params);
    }
}
