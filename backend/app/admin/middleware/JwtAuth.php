<?php
namespace app\admin\middleware;

use app\common\service\JwtService;

class JwtAuth
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('token') ?: $request->header('authorization');
        if (!$token) {
            return $this->json([], '未登录', 401);
        }
        try {
            $result = JwtService::verify($token);
        } catch (\Throwable $e) {
            $result = false;
        }
        if (!$result) {
            return $this->json([], '登录过期', 401);
        }
        return $next($request);
    }

    protected function json($data = [], $msg = 'success', $code = 200)
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);
    }
}
