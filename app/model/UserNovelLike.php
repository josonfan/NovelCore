<?php
declare(strict_types=1);

namespace app\model;

/**
 * 用户点赞小说关系模型。
 */
class UserNovelLike extends BaseModel
{
    protected $table = 'user_novel_likes';

    /**
     * 仅记录创建时间。
     * @var string|false
     */
    protected $updateTime = false;
}
