<?php
namespace app\common\service;

use app\common\model\Sites;
use app\common\model\SyncQueue;

class SyncService
{
    public static function enqueue(string $contentType, int $contentId, string $operation): void
    {
        try {
            $sites = (new Sites())->where('is_active', 1)->field('id')->select()->toArray();
            foreach ($sites as $s) {
                $siteId = (int)$s['id'];
                $existingId = (new SyncQueue())
                    ->where('site_id', $siteId)
                    ->where('content_type', $contentType)
                    ->where('content_id', $contentId)
                    ->value('id');
                $data = [
                    'site_id' => $siteId,
                    'content_type' => $contentType,
                    'content_id' => $contentId,
                    'operation' => $operation,
                    'status' => 'pending',
                    'attempts' => 0,
                    'last_error' => null,
                ];
                if ($existingId) {
                    (new SyncQueue())->writeById((int)$existingId, $data);
                } else {
                    (new SyncQueue())->writeById(0, $data);
                }
            }
        } catch (\Throwable $e) {}
    }
}
