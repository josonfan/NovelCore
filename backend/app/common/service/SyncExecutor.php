<?php
namespace app\common\service;

use app\common\model\SyncQueue;
use app\common\model\Sites;
use app\common\model\Categories;
use app\common\model\Tags;
use app\common\model\Novels;
use app\common\model\Chapters;
use app\common\model\ChapterContents;
use app\common\model\SiteInitRecord;

class SyncExecutor
{
    public static function run(int $limit = 20): int
    {
        self::runInit(200);
        $q = new SyncQueue();
        $list = $q->where('status', 'pending')->order('id asc')->limit($limit)->select()->toArray();
        $count = 0;
        foreach ($list as $row) {
            $ok = self::process($row);
            $count += $ok ? 1 : 0;
        }
        return $count;
    }

    protected static function runInit(int $batch = 200): void
    {
        $recs = (new SiteInitRecord())->where('status', 'in', ['queued','pushing'])->order('id asc')->select()->toArray();
        foreach ($recs as $rec) {
            $siteId = (int)$rec['site_id'];
            $type = (string)$rec['type'];
            $lastPk = (int)$rec['last_pk'];
            $model = self::modelByType($type);
            if (!$model) continue;
            (new SiteInitRecord())->writeById((int)$rec['id'], ['status' => 'pushing']);
            $rows = $model->where('id', '>', $lastPk)->order('id asc')->limit($batch)->field('id')->select()->toArray();
            if (empty($rows)) {
                (new SiteInitRecord())->writeById((int)$rec['id'], ['status' => 'completed']);
                continue;
            }
            $maxId = $lastPk;
            foreach ($rows as $r) {
                $rid = (int)$r['id'];
                $maxId = max($maxId, $rid);
                SyncService::enqueueForSite($siteId, $type, $rid, 'create');
            }
            (new SiteInitRecord())->writeById((int)$rec['id'], ['last_pk' => $maxId, 'status' => 'pushing']);
        }
    }

    protected static function modelByType(string $t)
    {
        if ($t === 'categories') return new Categories();
        if ($t === 'tags') return new Tags();
        if ($t === 'novels') return new Novels();
        if ($t === 'chapters') return new Chapters();
        return null;
    }

    // ctypeByType removed: using table name directly

    protected static function process(array $row): bool
    {
        $q = new SyncQueue();
        $q->writeById((int)$row['id'], ['status' => 'processing', 'attempts' => (int)$row['attempts'] + 1, 'last_error' => null]);
        $site = (new Sites())->cacheInfo((int)$row['site_id']);
        if (empty($site)) {
            $q->writeById((int)$row['id'], ['status' => 'failed', 'last_error' => 'site_not_found']);
            return false;
        }
        $includeData = (bool)config('sync.sync_data', true);
        $data = $includeData ? self::payload((string)$row['content_type'], (int)$row['content_id']) : [];
        $url = rtrim((string)$site['base_api_url'], '/') . config('sync.push_path', '/api/Sync/receive');
        $headers = ['Content-Type: application/json', 'X-Api-Token: ' . (string)$site['api_token']];
        $payload = json_encode(['type' => (string)$row['content_type'], 'id' => (int)$row['content_id'], 'operation' => (string)$row['operation'], 'data' => $data], JSON_UNESCAPED_UNICODE);
        $ok = self::postJson($url, $payload, $headers, (int)config('sync.timeout', 5));
        if ($ok) {
            $q->writeById((int)$row['id'], ['status' => 'success', 'last_error' => null]);
            return true;
        } else {
            $q->writeById((int)$row['id'], ['status' => 'failed', 'last_error' => 'push_failed']);
            return false;
        }
    }

    protected static function payload(string $type, int $id): array
    {
        if ($type === 'categories') {
            return (new Categories())->cacheInfo($id) ?: [];
        }
        if ($type === 'tags') {
            return (new Tags())->cacheInfo($id) ?: [];
        }
        if ($type === 'novels') {
            return (new Novels())->cacheInfo($id) ?: [];
        }
        if ($type === 'chapters') {
            $ch = (new Chapters())->cacheInfo($id) ?: [];
            if (!empty($ch)) {
                $content = (new ChapterContents())->cacheInfo($id);
                if (!empty($content) && isset($content['content'])) {
                    $ch['content'] = $content['content'];
                }
            }
            return $ch ?: [];
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
