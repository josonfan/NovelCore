<?php
namespace app\admin\middleware;

use app\common\service\JwtService;
use app\admin\service\RbacService;

class RbacAuth
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('token') ?: $request->header('authorization');
        if (!$token) {
            return json(['code'=>401,'msg'=>'未登录','data'=>[]]);
        }
        $claims = JwtService::decode($token);
        $uid = (int)($claims['uid'] ?? 0);
        if ($uid <= 0) {
            return json(['code'=>401,'msg'=>'未登录','data'=>[]]);
        }
        $resource = (string)$request->controller();
        $action = (string)$request->action();
        $ok = RbacService::check($uid, $resource, $action);
        if (!$ok) {
            return json(['code'=>403,'msg'=>'权限不足','data'=>[]]);
        }
        return $next($request);
    }
}

