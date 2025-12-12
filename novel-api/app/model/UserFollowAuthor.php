<?php
declare(strict_types=1);

namespace app\model;

/**
 * 用户关注作者关系模型。
 */
class UserFollowAuthor extends BaseModel
{
    protected $name = 'user_follow_authors';
    protected $pk = 'id';

    /**
     * 仅记录创建时间。
     * @var string|false
     */
    protected $updateTime = false;
}
