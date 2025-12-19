<?php
namespace app\controller;

use think\App;
use think\Request;

class Common
{
    protected $request;
    protected $app;
    protected $_data;
    public function __construct(App $app){
        $this->app     = $app;
        $this->request = $this->app->request;
        $this->_data = [];
        //判断是否是json请求

        if(!$this->request->isJson()){
            $this->_data = $this->request->param();
        }else{
            $this->_data = json_decode(file_get_contents('php://input'),true);
        }
        if(empty($this->_data)){
            $this->_data = $this->request->param();
        }
        $this->_data['timestamp'] = date('Y-m-d H:i:s', time());
    }
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