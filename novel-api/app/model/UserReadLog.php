<?php
declare(strict_types=1);

namespace app\model;

/**
 * 用户阅读行为明细模型。
 */
class UserReadLog extends BaseModel
{
    protected $name = 'user_read_logs';
    protected $pk = 'id';

    /**
     * 仅记录创建时间。
     * @var string|false
     */
    protected $updateTime = false;
}
