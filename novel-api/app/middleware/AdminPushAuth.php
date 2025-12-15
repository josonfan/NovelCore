<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;

class AdminPushAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = (string) $request->header('X-Api-Token', '');
        $expected = (string) config('site.api_token', '');
        if ($token === '' || $expected === '' || !hash_equals($expected, $token)) {
            return api_response(401, '未授权的后台推送', [])->code(401);
        }
        return $next($request);
    }
}
