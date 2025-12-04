<?php
declare(strict_types=1);

namespace app\model;

/**
 * 用户设备记录模型。
 */
class UserDeviceLog extends BaseModel
{
    protected $table = 'user_device_logs';

    /**
     * 仅记录创建时间。
     * @var string|false
     */
    protected $updateTime = false;
}
