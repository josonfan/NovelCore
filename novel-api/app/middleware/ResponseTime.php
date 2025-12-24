<?php
namespace app\middleware;

use think\Request;
use think\Response;
use Closure;
use think\facade\Log;

class ResponseTime
{
    public function handle(Request $request, Closure $next)
    {
        // 记录请求开始时间
        $startTime = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        // 计算响应时间
        $endTime = microtime(true);
        $responseTime = $endTime - $startTime;

        // 构建日志信息
        $timestamp = date('Y-m-d\TH:i:sP'); // 当前时间
        $logType = 'api'; // 根据需要自定义
        $url = $request->url(true); // 获取完整URL
        $method = $request->method(); // 获取请求方法
        if($method==='GET'){
            $params=$request->get();
        }else{
            $params = $request->post(); // 获取请求参数
        }
        $body=[
            'method'=>$method,
            'params'=>$params,
            'header'=>$request->header(),
            'ser_ip'=>$request->ip(),
        ];
        if($params){
            $params=json_encode($body); // 获取请求参数
        }else{
            $params='';
        }

        $logMessage = sprintf(
            "%s [ method:%s ] [ params:%s ] [ RunTime:%.6fs ]",
            $url,
            $method,
            $params,
            $responseTime
        );
        
        // 将响应时间记录到日志
        trace($logMessage, $logType);
        // Log::log($logType,$logMessage);
        return $response;
    }
}