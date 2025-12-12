<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;

class Auth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);
        if (!$token) {
            return $this->unauthorized();
        }

        try {
            $jwt = app(\app\service\JwtService::class);
            $payload = $jwt->parseToken($token);
        } catch (\app\exception\BusinessException $e) {
            return $this->unauthorized($e->getMessage());
        }

        $userId = (int) ($payload['uid'] ?? 0);
        if ($userId <= 0) {
            return $this->unauthorized();
        }

        $user = \app\model\User::find($userId);
        if (!$user || (int) $user->status !== 1) {
            return $this->unauthorized();
        }

        $request->user = $user;
        return $next($request);
    }

    protected function extractToken(Request $request): ?string
    {
        $authorization = $request->header('Authorization', '');
        if (str_starts_with($authorization, 'Bearer ')) {
            return trim(substr($authorization, 7));
        }
        return null;
    }

    protected function unauthorized(string $message = '未登录或令牌无效')
    {
        return api_response(401, $message, [])->code(401);
    }
}
