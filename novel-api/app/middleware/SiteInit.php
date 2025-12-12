<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;

class SiteInit
{
    private static bool $done = false;

    public function handle(Request $request, Closure $next)
    {
        if (!self::$done) {
            \app\service\SiteRegisterService::registerIfNeeded();
            self::$done = true;
        }
        return $next($request);
    }
}

