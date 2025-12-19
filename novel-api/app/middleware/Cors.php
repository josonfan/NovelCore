<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $path = (string) $request->pathinfo();
        $isApiRequest = str_starts_with($path, 'api/') || $path === 'api';

        if (!$isApiRequest) {
            return $next($request);
        }

        $headers = [
            'Access-Control-Allow-Origin'      => $request->header('Origin', '*'),
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Origin, Authorization, Content-Type, X-Requested-With, Client-Version, Device',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
        ];

        if ($request->isOptions()) {
            $response = response('', 204);
            foreach ($headers as $key => $value) {
                $response->header([$key => $value]);
            }
            return $response;
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->header([$key => $value]);
        }
        return $response;
    }
}
