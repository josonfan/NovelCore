<?php
declare(strict_types=1);

use think\Response;
use think\facade\Db;
if (!function_exists('filter_res_data')) {
    /**
     * 前端数据格式化
     */
    function filter_res_data($res){
        unset($res['sort']);
        unset($res['create_user']);
        //unset($res['create_time']);
        unset($res['update_user']);
        unset($res['create_user_name']);
        unset($res['update_user_name']);
        //unset($res['update_time']);
        unset($res['mark']);
        return $res;
    }
}
if (!function_exists('getArrayByFields')) {
    /**
     * 前端数据格式化
     */
    function getArrayByFields($data,$field='*'){
        if(!is_array($data))$data = $data->toArray();
        if($field=='*')$data =filter_res_data($data);
        if(!empty($data)&&$field!='*'){
            $array_key = [];
            $keys = explode(',',$field);
            foreach ($keys as $k){
                if(strstr($k, ' as ')){
                    $array = explode(" as ", $k);
                    $array_key[] = trim($array[1]);
                    $data[trim($array[1])] = $data[trim($array[0])];
                }else{
                    $array_key[] = $k;
                }
            }
            foreach ($data as $key=>$val){
                if(!in_array($key,$array_key)){
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }
}
if (!function_exists('getListByFields')) {
    /**
     * 前端数据格式化
     */
    function getListByFields($list,$field='*'){
        if(!empty($list)){
            foreach ($list as $key=>$val){
                $list[$key] = getArrayByFields($val,$field);
            }
        }
        return $list;
    }
}
if (!function_exists('getAsyncQueueKey')) {
    /**
     * 获取异步队列key
     * @param $uuid
     * @return string
     */
    function getAsyncQueueKey($uuid, $type = 'async_exec_method_custom_queue'): string
    {
        $queues = config('async.'.$type);
        $queue = array_keys($queues);        
        $queueIndex = hexdec(substr($uuid, 0, 8)) % count($queue);
        return $queue[$queueIndex];
    }
}
if (!function_exists('formatWhere')) {
    /**
     * tp官方数组查询方法废弃，数组转化为现有支持的查询方法
     * @param array $data 原始查询条件
     * @return array
     */
    function formatWhere($data){
        $where = [];
        foreach( $data as $k=>$v){
            if(is_array($v)){
                if(((string) $v[1] <> null && !is_array($v[1])) || (is_array($v[1]) && (string) $v[1][0] <> null)){
                    switch(strtolower($v[0])){
                        //模糊查询
                        case 'like':
                            $v[1] = '%'.$v[1].'%';
                            break;

                        //表达式查询
                        case 'exp':
                            $v[1] = Db::raw($v[1]);
                            break;
                    }
                    $where[] = [$k,$v[0],$v[1]];
                }
            }else{
                if((string) $v != null){
                    $where[] = [$k,'=',$v];
                }
            }
        }
        return $where;
    }
}

if (!function_exists('getUidByID')) {

    function getUidByID($id): int
    {
        return $id+env('SITE.ID_OFFSET');
    }
}
if (!function_exists('getIDByUid')) {

    function getIDByUid($uid): int
    {
        return $uid-env('SITE.ID_OFFSET');
    }
}
