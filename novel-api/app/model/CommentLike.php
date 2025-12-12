<?php
declare(strict_types=1);

namespace app\model;

/**
 * 评论点赞关系模型。
 */
class CommentLike extends BaseModel
{
    protected $name = 'comment_likes';
    protected $pk = 'id';

    /**
     * 仅记录创建时间。
     * @var string|false
     */
    protected $updateTime = false;
}
