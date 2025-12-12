<?php
declare(strict_types=1);

namespace app\service;

use app\model\Order;
use app\model\SystemConfig;
use app\model\User;
use GuzzleHttp\Client;
use think\facade\Log;

/**
 * 用户与订单上报服务：按 updated_at 增量推送至总后台。
 */
class ReportSyncService
{
    protected string $configKey = 'sync:last_user_order';

    /**
     * 主执行：收集增量并上报。
     */
    public function sync(?string $since = null): void
    {
        $sinceTime = $since ?: $this->getLastSyncTime();
        $now = date('Y-m-d H:i:s');

        $users  = $this->collectUsers($sinceTime);
        $orders = $this->collectOrders($sinceTime);

        $client = $this->makeClient();

        if (!empty($users)) {
            $this->postUsers($client, $users);
        }

        if (!empty($orders)) {
            $this->postOrders($client, $orders);
        }

        // 成功无异常则更新同步时间
        $this->setLastSyncTime($now);
    }

    protected function collectUsers(string $since): array
    {
        return User::whereTime('updated_at', '>', $since)
            ->field(['id', 'username', 'nickname', 'avatar', 'status', 'vip_expire', 'created_at', 'updated_at'])
            ->select()
            ->toArray();
    }

    protected function collectOrders(string $since): array
    {
        return Order::whereTime('updated_at', '>', $since)
            ->field(['order_no', 'user_id', 'amount', 'order_type', 'status', 'created_at', 'updated_at'])
            ->select()
            ->toArray();
    }

    protected function makeClient(): Client
    {
        return new Client([
            'base_uri' => rtrim((string) config('report.base_url', ''), '/'),
            'timeout'  => 5,
        ]);
    }

    protected function postUsers(Client $client, array $users): void
    {
        $this->postPayload($client, config('report.users_endpoint', '/api/report/users'), ['users' => $users]);
    }

    protected function postOrders(Client $client, array $orders): void
    {
        $this->postPayload($client, config('report.orders_endpoint', '/api/report/orders'), ['orders' => $orders]);
    }

    protected function postPayload(Client $client, string $endpoint, array $payload): void
    {
        $token = (string) config('report.token', '');
        if ($token === '') {
            throw new \RuntimeException('上报令牌未配置');
        }

        $client->post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'json' => $payload,
        ]);
    }

    protected function getLastSyncTime(): string
    {
        $record = SystemConfig::find($this->configKey);
        if ($record && $record->config_value) {
            return $record->config_value;
        }

        $seconds = (int) config('report.default_lookback', 3600);
        return date('Y-m-d H:i:s', time() - $seconds);
    }

    protected function setLastSyncTime(string $time): void
    {
        $record = SystemConfig::find($this->configKey);
        if ($record) {
            $record->config_value = $time;
            $record->save();
        } else {
            $cfg = new SystemConfig();
            $cfg->save([
                'config_key'   => $this->configKey,
                'config_value' => $time,
            ]);
        }
    }
}
