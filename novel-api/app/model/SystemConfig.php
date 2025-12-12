<?php
declare(strict_types=1);

namespace app\model;

/**
 * 系统配置键值表，键为 config_key。
 */
class SystemConfig extends BaseModel
{
    protected $name = 'system_config';
    protected $pk = 'config_key';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = false;
    protected $updateTime = 'updated_at';
}
