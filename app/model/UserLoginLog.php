<?php
declare(strict_types=1);

namespace app\model;

/**
 * 用户登录日志模型。
 */
class UserLoginLog extends BaseModel
{
    protected $table = 'user_login_logs';

    /**
     * 使用 login_time 作为创建时间，禁用更新时间。
     * @var string
     */
    protected $createTime = 'login_time';

    /**
     * 登录日志无更新字段。
     * @var string|false
     */
    protected $updateTime = false;
}
