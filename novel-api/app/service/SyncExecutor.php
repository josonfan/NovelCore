<?php
namespace app\service;

use app\model\SyncQueue;


class SyncExecutor
{
    public static function run(int $limit = 20): int
    {
        $q = new SyncQueue();
        $list = $q->where('status', 'pending')
        ->order('id asc')
        ->limit($limit)
        ->select()
        ->toArray();
        $count = 0;
        foreach ($list as $row) {
            $ok = self::process($row);
            $count += $ok ? 1 : 0;
        }
        return $count;
    }

    

    protected static function modelByType(string $t)
    {
        $parts = explode('_', $t);
        $class = '\\app\\model\\' . implode('', array_map(function($p){ return ucfirst($p); }, $parts));        
        return class_exists($class) ? new $class() : null;
    }

    // ctypeByType removed: using table name directly

    protected static function process(array $row): bool
    {
        $q = new SyncQueue();        
        $site = config('site');        
        $includeData = (bool)config('sync.sync_data', true);
        $data = $includeData ? self::payload((string)$row['content_type'], (int)$row['content_id']) : [];
        $url = rtrim((string)$site['base_api_url'], '/') . config('sync.push_path', '/Sync/receive');
        $headers = ['Content-Type: application/json', 'X-Api-Token: ' . (string)$site['api_token']];
        $payload = json_encode(['base_api_url' => (string)$site['base_api_url'], 'type' => (string)$row['content_type'], 'id' => (int)$row['content_id'], 'operation' => (string)$row['operation'], 'data' => $data], JSON_UNESCAPED_UNICODE);
        
        $ok = self::postJson($url, $payload, $headers, (int)config('sync.timeout', 5));
        if ($ok) {
            (new SyncQueue())->deleteById((int)$row['id']);           
            return true;
        } else {
            $q->writeById((int)$row['id'], ['status' => 'failed', 'last_error' => 'push_failed']);
            return false;
        }
    }

    protected static function payload(string $type, int $id): array
    {
        
        $m = self::modelByType($type);
        if ($m) {
            return $m->cacheInfo($id) ?: [];
        }
        return [];
    }

    protected static function postJson(string $url, string $json, array $headers, int $timeout): bool
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $resp = curl_exec($ch);
            $err = curl_errno($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $err === 0 && $code >= 200 && $code < 300;
        } else {
            $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => implode("\r\n", $headers), 'content' => $json, 'timeout' => $timeout]]);
            $resp = @file_get_contents($url, false, $ctx);
            return $resp !== false;
        }
    }
}
