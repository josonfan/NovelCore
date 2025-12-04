<?php
declare(strict_types=1);

namespace app\model;

/**
 * 用户搜索日志模型，记录关键字与筛选条件。
 */
class UserSearchLog extends BaseModel
{
    protected $table = 'user_search_logs';

    /**
     * 仅写入创建时间。
     * @var string|false
     */
    protected $updateTime = false;

    /**
     * JSON 自动转换字段。
     * @var array
     */
    protected $json = ['filters_json'];
}
