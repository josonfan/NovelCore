<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);
        $expected = (string) config('admin.admin_token', '');
        if ($expected === '' || $token !== $expected) {
            return api_response(401, '未授权的后台访问', [])->code(401);
        }
        return $next($request);
    }

    protected function extractToken(Request $request): string
    {
        $auth = $request->header('Authorization', '');
        if (str_starts_with($auth, 'Bearer ')) {
            return trim(substr($auth, 7));
        }
        return (string) $request->header('X-Admin-Token', '');
    }
}
