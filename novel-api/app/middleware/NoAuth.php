<?php
namespace app\middleware;

use Closure;
use think\Request;

class NoAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);
        if ($token) {
            try {
                $jwt = app(\app\service\JwtService::class);
                $payload = $jwt->parseToken($token);
            } catch (\app\exception\BusinessException $e) {                         
            }
            $userId = (int) ($payload['uid'] ?? 0);
            if ($userId > 0) {
                $request->user_id = $userId;
            }
        }

        
        
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

}