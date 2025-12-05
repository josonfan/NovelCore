<?php
namespace app\admin\middleware;

use app\common\service\JwtService;

class JwtAuth
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('token') ?: $request->header('authorization');
        if (!$token) {
            return $this->json( '未登录', 401);
        }
        try {
            $result = JwtService::verify($token);
        } catch (\Throwable $e) {
            $result = false;
        }
        if (!$result) {
            return $this->json( '登录过期', 401);
        }
        $claims = JwtService::decode($token);
        $jti = (string)($claims['jti'] ?? '');
        if ($jti !== '' && \think\facade\Cache::get('jwt_blacklist_' . $jti)) {
            return $this->json( '已退出', 401);
        }
        $request->uid = (int)($claims['uid'] ?? 0);
        return $next($request);
    }

    protected function json($msg = 'success', $code = 200)
    {
        
        return json([
            'code' => $code,
            'msg' => $msg,
        ]);
    }
}
