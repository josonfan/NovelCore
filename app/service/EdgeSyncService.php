<?php
declare(strict_types=1);

namespace app\service;

use app\model\Chapter;
use app\model\ChapterContent;
use app\model\Novel;

/**
 * 边缘同步占位（Cloudflare Workers/D1）。
 */
class EdgeSyncService
{
    /**
     * 预期行为：将章节内容同步到边缘存储（如 Cloudflare D1/R2），以便靠近用户侧读取。
     * 当前仅占位，不做实际网络调用。
     */
    public function syncChapterToEdge(Novel $novel, Chapter $chapter, ChapterContent $content): void
    {
        // TODO: 调用 Cloudflare API 写入 D1/R2，并处理鉴权/重试/幂等
    }

    /**
     * 预期行为：当小说更新时，刷新边缘缓存（如 Workers KV/Cache）。
     * 当前仅占位，不做实际网络调用。
     */
    public function invalidateEdgeCacheForNovel(Novel $novel): void
    {
        // TODO: 调用 Cloudflare API 进行缓存刷新或 KV 失效
    }
}
