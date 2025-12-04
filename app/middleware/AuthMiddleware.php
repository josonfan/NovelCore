<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;

class AuthMiddleware
{
    /**
     * 鉴权中间件：校验 JWT 并加载当前用户。
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);
        if (!$token) {
            return $this->unauthorized();
        }

        try {
            /** @var JwtService $jwt */
            $jwt = app(JwtService::class);
            $payload = $jwt->parseToken($token);
        } catch (BusinessException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $userId = (int) ($payload['uid'] ?? 0);
        if ($userId <= 0) {
            return $this->unauthorized();
        }

        $user = User::find($userId);
        if (!$user || (int) $user->status !== 1) {
            return $this->unauthorized();
        }

        // 将当前用户挂载到请求对象，便于后续控制器获取
        $request->user = $user;

        return $next($request);
    }

    /**
     * 从 Authorization 头中提取 Bearer Token。
     */
    protected function extractToken(Request $request): ?string
    {
        $authorization = $request->header('Authorization', '');
        if (str_starts_with($authorization, 'Bearer ')) {
            return trim(substr($authorization, 7));
        }

        return null;
    }

    /**
     * 返回未登录/令牌无效的统一响应。
     */
    protected function unauthorized(string $message = '未登录或令牌无效')
    {
        return json_error($message, 401, [])->code(401);
    }
}
