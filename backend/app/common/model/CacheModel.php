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
    /**
     * 获取列表
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @param string $orderby 排序字段
     * @param int $limit 每页数量
     * @param int $page 页码
     * @return array
     */
    public function getList(array $where = [], string $field = '*', string $orderby = '', int $limit = 10, int $page = 1): array
    {
        try {
            $fields = $this->is_cache ? $this->getPk() : $field;
            $res = $this->where($where)->field($fields)->order($orderby)->paginate([
                'list_rows' => $limit,
                'page' => $page,
            ])->toArray();
            if ($this->is_cache && !empty($res['data'])) {
                foreach ($res['data'] as $key => $val) {
                    $res['data'][$key] = $this->infoById((int)$val[$this->getPk()], $field);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return ['list' => $res['data'], 'count' => $res['total']];
    }
    /**
     * 获取缓存数据
     * @param int $id 主键值
     * @param int $ttl 缓存过期时间
     * @return array|false
     */
    public function infoById(int $id, $field = '*')
    {
        $ttl = env('CACHE.TTL', 600);
        if (!$this->is_cache) {
            $data = $this->cacheInfo($id);
        }else{
            $key = $this->getCacheKey($id);
            $data = $this->getCache($key);
            if ($data === false || $data === null) {
                $data = $this->cacheInfo($id);
                $this->setCacheData($key, $data, $ttl);
            }
        }
        
        return getArrayByFields($data,$field);
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
            return Cache::delete($cache_key);
        }
        $result = false;
        $isGzcompress = gzcompress(json_encode($data));
        if ($isGzcompress) {
            $result = Cache::set($cache_key, $isGzcompress, $ttl);
        }
        return $result;
    }
    /**
     * 异步写入缓存数据
     * @param int $id 主键值
     * @param array $data 缓存数据
     * @param int $ttl 缓存过期时间
     * @return bool
     */
    public function writeById(int $id, array $data, int $ttl = 0): bool
    {
        if (!$this->is_cache) {
            try {
                $pk = $this->getPk();
                return (bool)$this->where($pk, $id)->save($data);
            } catch (\Throwable $e) {
                return false;
            }
        }
        $key = $this->getCacheKey($id);
        $ok = $this->setCacheData($key, $data, $ttl);
        $updKey = $this->getCacheKey($id, 'upd');
        $this->setCacheData($updKey, $data, 0);
        Async::delayUseCustomQueue(0, \app\common\model\CacheModel::class, 'persistByIdRef', getAsyncQueueKey(md5((string)$id)), static::class, $id, $updKey);
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
    /**
     * 异步持久化缓存数据
     * @param string $modelClass 模型类名
     * @param int $id 主键值
     * @param array $data 缓存数据
     * @return bool
     */
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
    /**
     * 异步持久化缓存数据
     * @param string $modelClass 模型类名
     * @param int $id 主键值
     * @param string $updKey 缓存键名
     * @return bool
     */
    public static function persistByIdRef(string $modelClass, int $id, string $updKey): bool
    {
        try {
            $m = new $modelClass();
            $pk = $m->getPk();
            $data = Cache::get($updKey);
            if ($data) {
                $data = json_decode(gzuncompress($data), true);
            }
            if (!$data || !is_array($data)) {
                return false;
            }
            self::persistById($modelClass, $id, $data);
            Cache::delete($updKey);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
