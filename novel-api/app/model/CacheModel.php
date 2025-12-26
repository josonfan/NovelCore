<?php
namespace app\model;

use think\Model;
use think\facade\Cache;
use think\facade\Db;
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
        if(env('QUEUE.DRIVER', 'sync')==='sync'){
            $this->is_cache = false;
        }
        $this->db_prefix = env('DATABASE.PREFIX', 'bl_');
    }
    /**
     * 获取缓存键名
     * @return string
     */
    public function getCacheKey()
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
    public function infoById($id, $field = '*'): array
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
            $result = Cache::set($cache_key, $isGzcompress, (int)$ttl);
        }
        return $result;
    }
    /**
     * 异步写入缓存数据
     * @param int $id 主键值
     * @param array $data 缓存数据
     * @return bool
     */
    public function writeById($id, array $data,$upDb=false): bool
    {
        if (!$this->is_cache || $upDb) {     
            $ok = self::persistById(static::class, $id, $data);
            return $ok;
        } 
        $ttl = env('CACHE.TTL', 600);
        if (empty($id)) {
            $updKey = $this->getCacheKey(md5(json_encode($data, JSON_UNESCAPED_UNICODE)), 'upd');
            $info = $data;
        }else{
            $info = $this->infoById($id);
            foreach ($data as $k => $v) {
                $info[$k] = $v;
            }
            $key = $this->getCacheKey($id);
            $this->setCacheData($key, $info, $ttl);
            $updKey = $this->getCacheKey($id, 'upd');
        }
        $ok = $this->setCacheData($updKey, $info, 0);
        Async::delayUseCustomQueue(0, \app\model\CacheModel::class, 'persistByIdRef', getAsyncQueueKey(md5((string)$id)), static::class, $id, $updKey);
        return $ok;
    }
    
    /**
     * 获取缓存状态
     * @param string $cache_key 缓存键名
     * @return bool
     */
    public  function getCacheStatus($cache_key)
    {
        return Cache::has($cache_key);
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
        $data = $this->find($id);
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
    public static function persistById(string $modelClass, $id, array $data): bool
    {  
        try {            
            $m = new $modelClass();            
            $pk = $m->getPk();
            if (empty($id)) {
                $id = $data[$pk]??0;
            }
            if (method_exists($m, 'filterAllowedFields')) {
                $data = $m->filterAllowedFields($data);
            }
            if(empty($id)){ 
                $res =  (bool)$m->save($data);  
                $id = (int)($m->$pk ?? 0);
                $op = 'create';
            }else{
                try{
                    $old = $m->where($pk,$id)->value($pk);
                    if(empty($old)){
                        $res = (bool)$m->insert($data);
                    }else{
                        unset($data[$pk]);
                        $res = (bool)$m->where($pk,$id)->save($data);
                    }
                }catch(\Throwable $e){
                    dd($e);
                    trace($e->getMessage(),'error');
                    return false;
                }                
                
                $op = 'update';
            }
            if ($res) {
                if($id > 0){
                    $key = $m->getCacheKey($id);
                    Cache::delete($key);
                }
                $name = method_exists($m,'getName') ? (string)$m->getName() : '';
                $type = self::contentTypeFromTable($name);
                $enabled = (array)(config('sync.enable_types') ?? []);
                if ($type && in_array($type, $enabled, true)) {
                    \app\service\SyncService::enqueue($type, (int)$id, $op);
                }                
            }
            return $res;
        } catch (\Throwable $e) {
            trace($e->getMessage(),'error');            
            return false;
        }
    }
    /**
     * 从表名获取内容类型
     * @param string $name 表名
     * @return ?string
     */
    protected static function contentTypeFromTable(string $name): ?string
    {
        switch ($name) {
            case 'users': return 'user';
            case 'comments': return 'comment';
            case 'orders': return 'order';
            case 'stats': return 'stats';
            default: return null;
        }
    }
    /**
     * 异步持久化缓存数据
     * @param string $modelClass 模型类名
     * @param int $id 主键值
     * @param string $updKey 缓存键名
     * @return bool
     */
    public static function persistByIdRef(string $modelClass, $id, string $updKey): bool
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

    /**
     * 预热主键缓存
     * @param int $id 主键值
     * @param array $data 数据
     * @return bool
     */
    public function primeCacheById($id, array $data): bool
    {
        if (!$this->is_cache) {
            return true;
        }
        $ttl = env('CACHE.TTL', 600);
        $key = $this->getCacheKey($id);
        return $this->setCacheData($key, $data, $ttl);
    }
    public function deleteById($id): bool
    {
        if (!$this->is_cache) {
            return self::persistDelete(static::class, $id);
        }
        $updKey = $this->getCacheKey($id, 'del');
        $ok = $this->setCacheData($updKey, ['id' => $id], 0);
        Async::delayUseCustomQueue(0, 
            \app\model\CacheModel::class, 
            'persistDeleteRef', 
            getAsyncQueueKey(md5((string)$id)), 
            static::class, 
            $id, 
            $updKey
        );
        return (bool)$ok;
    }
    public static function persistDelete(string $modelClass, $id): bool
    {
        try {
            $m = new $modelClass();
            $pk = $m->getPk();
            if (empty($id)) {
                return false;
            }
            return (bool)$m->where($pk, $id)->delete();
        } catch (\Throwable $e) {
            return false;
        }
    }
    public static function persistDeleteRef(string $modelClass, $id, string $updKey): bool
    {
        try {
            $data = Cache::get($updKey);
            if ($data) {
                $data = json_decode(gzuncompress($data), true);
            }
            if (!$data || !is_array($data)) {
                return false;
            }
            $res = self::persistDelete($modelClass, $id);
            Cache::delete($updKey);
            return (bool)$res;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function filterAllowedFields(array $data, array $fallback = []): array
    {
        $table = $this->getTable();
        $fields = [];
        try {
            $fields = Db::getTableFields($table);
        } catch (\Throwable $e) {
            try {
                $cols = Db::query('SHOW COLUMNS FROM ' . $table);
                foreach ((array)$cols as $col) {
                    if (isset($col['Field'])) {
                        $fields[] = $col['Field'];
                    }
                }
            } catch (\Throwable $e2) {
                $fields = $fallback;
            }
        }
        if (!$fields) {
            return [];
        }
        $allowed = array_fill_keys($fields, true);
        return array_intersect_key($data, $allowed);
    }

}
