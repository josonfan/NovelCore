<?php
declare(strict_types=1);

namespace app\model;

/**
 * 用户阅读行为明细模型。
 */
class UserReadLog extends BaseModel
{
    protected $table = 'user_read_logs';

    /**
     * 仅记录创建时间。
     * @var string|false
     */
    protected $updateTime = false;
}
