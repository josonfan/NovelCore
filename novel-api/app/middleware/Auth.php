<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use app\exception\BusinessException;

class Auth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);
        if (!$token) {
            throw new BusinessException('未登录或令牌无效', 401);
        }

        try {
            $jwt = app(\app\service\JwtService::class);
            $payload = $jwt->parseToken($token);
        } catch (\app\exception\BusinessException $e) {
            throw new BusinessException($e->getMessage(), 401);
        }
        $userId = (int) ($payload['uid'] ?? 0);
        if ($userId <= 0) {
            throw new BusinessException('未登录或令牌无效', 401);
        }
        $request->user_id = $userId;
        return $next($request);
    }

    protected function extractToken(Request $request): ?string
    {
        $authorization = trim((string) $request->header('Authorization', ''));
        if ($authorization === '') {
            return null;
        }
        if (str_starts_with($authorization, 'Bearer ')) {
            return trim(substr($authorization, 7));
        }
        return $authorization;
    }

    // 统一由全局异常处理器拦截，无需在中间件内构造响应
    
}
