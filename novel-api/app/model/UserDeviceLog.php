<?php
declare(strict_types=1);

namespace app\model;

/**
 * 用户设备记录模型。
 */
class UserDeviceLog extends BaseModel
{
    protected $name = 'user_device_logs';
    protected $pk = 'id';

    /**
     * 仅记录创建时间。
     * @var string|false
     */
    protected $updateTime = false;
}
