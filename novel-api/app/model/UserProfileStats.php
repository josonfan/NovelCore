<?php
declare(strict_types=1);

namespace app\model;

/**
 * 用户个人统计表 user_profile_stats。
 */
class UserProfileStats extends BaseModel
{
    protected $name = 'user_profile_stats';
    protected $pk = 'user_id';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
}
