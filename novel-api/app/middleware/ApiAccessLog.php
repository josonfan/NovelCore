<?php
declare(strict_types=1);

namespace app\middleware;

use app\support\ApiLogger;
use Closure;
use Ramsey\Uuid\Uuid;
use think\Request;

class ApiAccessLog
{
    public function handle(Request $request, Closure $next)
    {
        $pathinfo = $request->pathinfo();
        if (!str_starts_with($pathinfo, 'api')) {
            return $next($request);
        }

        $start = microtime(true);
        $traceId = $request->header('X-Request-Id', '') ?: Uuid::uuid4()->toString();
        $request->trace_id = $traceId;

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            $durationMs = (microtime(true) - $start) * 1000;
            ApiLogger::logRequest($request, null, $durationMs, $traceId, $e);
            throw $e;
        }

        $durationMs = (microtime(true) - $start) * 1000;
        ApiLogger::logRequest($request, $response, $durationMs, $traceId, null);
        $response->header(['X-Request-Id' => $traceId]);
        return $response;
    }
}
