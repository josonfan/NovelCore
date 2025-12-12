<?php
declare(strict_types=1);

namespace app\model;

/**
 * 用户关注小说关系模型。
 */
class UserFollowNovel extends BaseModel
{
    protected $name = 'user_follow_novels';
    protected $pk = 'id';

    /**
     * 仅记录创建时间。
     * @var string|false
     */
    protected $updateTime = false;
}
