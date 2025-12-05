<?php
namespace app\admin\controller;

use app\BaseController;

class Backend extends BaseController
{
    // 
    /**
     * ajax返回
     * @param int $status 状态码
     * @param string $msg 提示信息
     * @param mixed $data 数据
     * @param string $token token
     * @return json
     */
    protected function ajaxReturn($status,$msg,$data=null,$token=''){
        $res = ['code'=>$status,'msg'=>$msg];
        isset($data) && $res['data'] = $data;
        !empty($token) && $res['token'] = $token;
        return json($res);
    }
}