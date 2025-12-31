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
        if ($row['operation'] !== 'delete' && empty($data)) {
            $q->deleteById((int)$row['id']);
            trace('sync_queue delete id: ' . json_encode($row) . ' data_empty');
            return false;
        }
        $adminApiUrl = (string) config('server.admin_api_url', '');
        $url = rtrim($adminApiUrl, '/') . config('sync.push_path', '/Sync/receive');
        $headers = ['Content-Type: application/json', 'X-Api-Token: ' . (string)$site['api_token']];
        $payload = json_encode(['code' => (string)$site['code'], 'type' => (string)$row['content_type'], 'id' => (int)$row['content_id'], 'operation' => (string)$row['operation'], 'data' => $data], JSON_UNESCAPED_UNICODE);
        // dd($payload);
        $ok = self::postJson($url, $payload, $headers, (int)config('sync.timeout', 5));
        if ($ok) {
            $q->deleteById((int)$row['id']);           
            return true;
        } else {
            $q->writeById((int)$row['id'], ['status' => 'failed', 'last_error' => 'push_failed']);
            trace('sync_queue delete id: ' . json_encode($row) . ' push_failed');
            return false;
        }
    }

    protected static function payload(string $type, int $id): array
    {
        
        $m = self::modelByType($type);
        if ($m) {
            return $m->infoById($id) ?: [];
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
            $bodyOk = true;
            if (is_string($resp) && $resp !== '') {
                $j = json_decode($resp, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($j)) {
                    if (isset($j['code']) && (int)$j['code'] === 422) {
                        $bodyOk = false;
                    }
                    if (isset($j['data']) && is_array($j['data']) && array_key_exists('saved', $j['data']) && $j['data']['saved'] === false) {
                        $bodyOk = false;
                    }
                }
            }
            return $err === 0 && $code >= 200 && $code < 300 && $bodyOk;
        } else {
            $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => implode("\r\n", $headers), 'content' => $json, 'timeout' => $timeout]]);
            $resp = @file_get_contents($url, false, $ctx);
            if ($resp === false) {
                return false;
            }
            $j = json_decode($resp, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($j)) {
                if ((isset($j['code']) && (int)$j['code'] === 422) || (isset($j['data']['saved']) && $j['data']['saved'] === false)) {
                    return false;
                }
            }
            return true;
        }
    }
}
