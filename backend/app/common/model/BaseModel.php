<?php
namespace app\common\model;


class BaseModel extends CacheModel
{
    // 
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $lastLogin_at = 'last_login_at';
}