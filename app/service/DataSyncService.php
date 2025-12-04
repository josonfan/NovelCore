<?php
declare(strict_types=1);

namespace app\service;

use app\model\Comment;
use app\model\Order;
use app\model\User;

/**
 * 数据上报服务：按 updated_at 过滤增量数据，供定时任务调用。
 */
class DataSyncService
{
    /**
     * 收集用户增量数据。
     */
    public function collectUsers(string $since): array
    {
        return User::whereTime('updated_at', '>', $since)
            ->field(['id', 'username', 'nickname', 'avatar', 'vip_expire', 'status', 'updated_at'])
            ->select()
            ->toArray();
    }

    /**
     * 收集订单增量数据。
     */
    public function collectOrders(string $since): array
    {
        return Order::whereTime('updated_at', '>', $since)
            ->field(['id', 'order_no', 'user_id', 'amount', 'order_type', 'status', 'created_at', 'updated_at'])
            ->select()
            ->toArray();
    }

    /**
     * 收集评论增量数据。
     */
    public function collectComments(string $since): array
    {
        return Comment::whereTime('updated_at', '>', $since)
            ->field(['id', 'novel_id', 'chapter_id', 'user_id', 'content', 'status', 'created_at', 'updated_at'])
            ->select()
            ->toArray();
    }

    /**
     * 总调度：组合三类数据，预留对接后台 API 的位置。
     */
    public function collectAll(string $since): array
    {
        return [
            'users'    => $this->collectUsers($since),
            'orders'   => $this->collectOrders($since),
            'comments' => $this->collectComments($since),
        ];
    }

    /**
     * 预留上报逻辑：未来在此处调用后台管理系统 API。
     */
    public function report(array $payload): void
    {
        // TODO: 调用后台接口推送 $payload，并处理重试/断点续传
    }
}
