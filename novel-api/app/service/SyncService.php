<?php
namespace app\service;

use app\model\SyncQueue;

class SyncService
{
    public static function enqueue(string $contentType, int $contentId, string $operation): void
    {
        try {
            $existingId = (new SyncQueue())
                ->where('content_type', $contentType)
                ->where('content_id', $contentId)
                ->value('id');
            $data = [
                'content_type' => $contentType,
                'content_id' => $contentId,
                'operation' => $operation,
                'status' => 'pending',
                'attempts' => 0,
                'last_error' => null,
            ];
            if ($existingId) {
                (new SyncQueue())->writeById((int)$existingId, $data, 'sync_async_exec_method_custom_queue');
            } else {
                (new SyncQueue())->writeById(0, $data, 'sync_async_exec_method_custom_queue');    
            }
        } catch (\Throwable $e) {}
    }

}
