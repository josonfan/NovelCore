<?php
declare(strict_types=1);

namespace app\service;

use app\model\SystemConfig;
use think\facade\Cache;

/**
 * 统一配置服务：优先缓存，未命中查询 system_config 并回填。
 */
class ConfigService
{
    /**
     * 获取配置（module 对应 config:<module>）。
     */
    public static function get(string $module, mixed $default = null): mixed
    {
        $key = self::buildKey($module);

        if (Cache::has($key)) {
            return self::decode(Cache::get($key), $default);
        }

        $record = SystemConfig::find($key);
        if (!$record) {
            return $default;
        }

        Cache::set($key, $record->config_value);
        return self::decode($record->config_value, $default);
    }

    /**
     * 写入缓存（保存数据库后调用）。
     */
    public static function setCache(string $module, array|string $value): void
    {
        $key = self::buildKey($module);
        $json = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json !== false) {
            Cache::set($key, $json);
        }
    }

    protected static function buildKey(string $module): string
    {
        return str_starts_with($module, 'config:') ? $module : 'config:' . $module;
    }

    protected static function decode(string $json, mixed $default): mixed
    {
        $data = json_decode($json, true);
        return $data === null ? $default : $data;
    }

    /**
     * 获取搜索配置（config:search）。
     */
    public function getSearchConfig(): array
    {
        $config = self::get('search', []);
        return is_array($config) ? $config : [];
    }
}
