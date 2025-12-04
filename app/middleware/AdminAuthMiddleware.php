<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;

/**
 * 后台接口鉴权中间件：校验 Admin Token。
 */
class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);
        $expected = (string) config('admin.admin_token', '');

        if ($expected === '' || $token !== $expected) {
            return json_error('未授权的后台访问', 401)->code(401);
        }

        return $next($request);
    }

    /**
     * 从请求头提取后台令牌，优先 Authorization: Bearer。
     */
    protected function extractToken(Request $request): string
    {
        $auth = $request->header('Authorization', '');
        if (str_starts_with($auth, 'Bearer ')) {
            return trim(substr($auth, 7));
        }

        return (string) $request->header('X-Admin-Token', '');
    }
}
