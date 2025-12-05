<?php
// 应用公共文件

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
    function getAsyncQueueKey($uuid): string
    {
        $queues = config('async.async_exec_method_custom_queue');
        $queue = array_keys($queues);        
        $queueIndex = hexdec(substr($uuid, 0, 8)) % count($queue);
        return $queue[$queueIndex];
    }
}