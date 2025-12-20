<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

class BotBlock
{
    public function handle(Request $request, Closure $next)
    {
        $path = $request->pathinfo();
        if (!str_starts_with($path, 'api')) {
            return $next($request);
        }

        $authorization = (string) $request->header('authorization', '');
        if ($authorization !== '') {
            return $next($request);
        }

        $ua = (string) $request->header('user-agent', '');
        $isBot = $ua === '' || preg_match('/bot|spider|crawl|crawler|curl|wget|python-requests|go-http-client|httpclient|scrapy|java|apache-http/i', $ua);
        if ($isBot) {
            return Response::create([
                'code' => 403,
                'msg'  => lang('禁止爬虫访问'),
                'data' => null,
            ], 'json', 403);
        }

        return $next($request);
    }
}
