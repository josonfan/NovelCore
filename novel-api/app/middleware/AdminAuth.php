<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use app\exception\BusinessException;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);
        $expected = (string) config('admin.admin_token', '');
        if ($expected === '' || $token !== $expected) {
            throw new BusinessException('未授权的后台访问', 401);
        }
        return $next($request);
    }

    protected function extractToken(Request $request): string
    {
        $auth = trim((string) $request->header('Authorization', ''));
        if ($auth !== '') {
            if (str_starts_with($auth, 'Bearer ')) {
                return trim(substr($auth, 7));
            }
            return $auth;
        }
        return (string) $request->header('X-Admin-Token', '');
    }
}
