<?php
namespace app\common\model;

class BaseConfig extends BaseModel
{
    protected $name = 'base_config';
    protected $pk = 'id';
    
    // 设置json类型字段
    protected $json = ['config_data'];
    
    // 设置JSON字段数组
    protected $jsonAssoc = true;
}
