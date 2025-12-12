<?php
declare(strict_types=1);

namespace app\support;

use think\Request;
use think\Response;

/**
 * API 访问日志记录，输出 NDJSON 便于 ELK/Filebeat 采集。
 */
class ApiLogger
{
    /**
     * 记录一次 API 请求日志。
     */
    public static function logRequest(Request $request, ?Response $response, float $durationMs, string $traceId, ?\Throwable $e = null): void
    {
        $statusCode = 200;
        if ($response) {
            $statusCode = $response->getCode();
        } elseif ($e && method_exists($e, 'getStatusCode')) {
            $statusCode = (int) $e->getStatusCode();
        } elseif ($e) {
            $statusCode = 500;
        }

        // 日志级别判定
        if ($e || $statusCode >= 500) {
            $level = 'ERROR';
        } elseif ($statusCode >= 400) {
            $level = 'WARNING';
        } else {
            $level = 'INFO';
        }

        $method = $request->method();
        $path = '/' . ltrim($request->baseUrl(), '/');
        $uid = null;
        if (isset($request->user) && is_object($request->user) && isset($request->user->id)) {
            $uid = $request->user->id;
        }

        $error = $e ? (get_class($e) . ': ' . $e->getMessage()) : null;

        $log = [
            'timestamp' => date('c'),
            'level'     => $level,
            'message'   => sprintf('%s %s %d', $method, $path, $statusCode),
            'context'   => [
                'method'      => $method,
                'path'        => $path,
                'uid'         => $uid,
                'duration_ms' => $durationMs,
                'status_code' => $statusCode,
                'trace_id'    => $traceId,
                'ip'          => $request->ip(),
                'error'       => $error,
                'extra'       => [
                    'query_string' => $request->server('QUERY_STRING') ?: '',
                ],
            ],
        ];

        $dir = runtime_path() . 'logs' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = $dir . 'api_' . date('Y-m-d') . '.log';
        $line = json_encode($log, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
