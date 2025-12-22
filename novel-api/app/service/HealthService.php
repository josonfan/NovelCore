<?php
declare(strict_types=1);

namespace app\service;

class HealthService
{
    /**
     * 获取指定队列的长度
     * @param string $queueName 队列名称
     * @return int 队列长度
     */
    public static function getQueueLength(string $queueName): int
    {
        
        return (int)\Baiy\ThinkAsync\Facade\Async::queueSize($queueName);
    }

    /**
     * 获取所有队列的信息
     * @return array 队列信息数组
     */
    public static function getQueue(): array
    {
        $queueNames = config('async.async_exec_method_custom_queue');
        $syncQueueNames = config('async.sync_async_exec_method_custom_queue');
        $queueNames = array_merge($queueNames,$syncQueueNames);
        $queueLengths = [];
        foreach ($queueNames as $key => $description) {
            $queueLengths[$key] = \Baiy\ThinkAsync\Facade\Async::queueSize($key);
        }
        
        return $queueLengths;
    }
    /** 
     * 数据库连接状态
     * @return bool 数据库连接状态
     */
    public static function getDbStatus(): bool
    {
        try {
            \think\facade\Db::query('select 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    /** 
     * 缓存连接状态
     * @return bool 缓存连接状态
     */
    public static function getCacheStatus(): bool
    {
        try {
            \think\facade\Cache::get('health-check');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}