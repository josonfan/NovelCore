<?php
namespace app\common\model;

use think\Model;
use think\facade\Cache;
use Baiy\ThinkAsync\Facade\Async;
/**
 * 缓存模型
 * @package app\common\model
 */
class CacheModel extends Model
{
    // 是否启用缓存
    protected $is_cache = true;
    protected $db_prefix = 'bl_';
    public function __construct(array|object $data = [])
    {
        parent::__construct($data);
        $this->is_cache = env('CACHE.IS_CACHE', true);
        $this->db_prefix = env('DATABASE.PREFIX', 'blad_');
    }
    /**
     * 获取缓存键名
     * @return string
     */
    private function getCacheKey()
    {
        $arg_list = func_get_args();
        if ($this->name) {
            array_unshift($arg_list, $this->db_prefix . $this->name);
        }
        foreach ($arg_list as $key => $val) {
            if (is_array($val)) {
                unset($arg_list[$key]);
            }
        }
        $cache_key = implode("_", $arg_list);
        return $cache_key;
    }
    public function infoById(int $id, int $ttl = 600)
    {
        $key = $this->getCacheKey($id);
        $data = $this->getCache($key);
        if ($data === false || $data === null) {
            $data = $this->cacheInfo($id);
            $this->setCacheData($key, $data, $ttl);
        }
        return $data;
    }
    /**
     * 设置缓存数据
     * @param string $cache_key 缓存键名
     * @param array $data 缓存数据
     * @param int $ttl 缓存过期时间
     * @return bool
     */
    public function setCacheData($cache_key, $data, $ttl = 0)
    {
        // 设置缓存池
        if (isset($GLOBALS['trans']) && $GLOBALS['trans'] === true) {
            $GLOBALS['trans_keys'][] = $cache_key;
        }
        // 不设置缓存，直接返回
        if (!$this->is_cache) {
            return true;
        }
        if (!$data) {
            return Cache::set($cache_key, null);
        }
        $result = false;
        $isGzcompress = gzcompress(json_encode($data));
        if ($isGzcompress) {
            $result = Cache::set($cache_key, $isGzcompress, $ttl);
        }
        return $result;
    }
    public function writeById(int $id, array $data, int $ttl = 0): bool
    {
        $key = $this->getCacheKey($id);
        $ok = $this->setCacheData($key, $data, $ttl);
        Async::delayUseCustomQueue(0, \app\common\model\CacheModel::class, 'persistById', getAsyncQueueKey(md5((string)$id)), static::class, $id, $data);
        return $ok;
    }
    /**
     * 获取缓存数据
     * @param string $cache_key 缓存键名
     * @return array|false
     */
    public function getCache($cache_key)
    {
        if (!$cache_key) {
            return false;
        }
        $data = Cache::get($cache_key);
        if ($data) {
            $data = json_decode(gzuncompress($data), true);
        }
        return $data;
    }
    /**
     * 获取缓存数据
     * @param int $id
     * @return array|false
     */
    public function cacheInfo($id)
    {
        if (!$id) {
            return false;
        }
        $data = $this->find((int)$id);
        // 获取对象原始数据,如果不存在指定字段返回false
        $data = !empty($data) ? $data->toArray() : [];
        return $data;
    }
    public static function persistById(string $modelClass, int $id, array $data): bool
    {
        try {
            $m = new $modelClass();
            $pk = $m->getPk();
            return (bool)$m->where($pk, $id)->save($data);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
