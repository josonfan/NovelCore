<?php
declare(strict_types=1);

namespace app\model;

/**
 * 用户阅读进度历史模型。
 */
class UserReadingHistory extends BaseModel
{
    protected $table = 'user_reading_history';

    /**
     * 使用 last_read_at 作为时间戳写入点，禁用更新时间。
     * @var string
     */
    protected $createTime = 'last_read_at';

    /**
     * 历史记录无更新字段。
     * @var string|false
     */
    protected $updateTime = false;
}
