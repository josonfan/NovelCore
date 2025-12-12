<?php
declare(strict_types=1);

namespace app\model;

/**
 * 每日运营统计表 daily_stats。
 */
class DailyStats extends BaseModel
{
    protected $name = 'daily_stats';
    protected $pk = 'id';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = false;
}
