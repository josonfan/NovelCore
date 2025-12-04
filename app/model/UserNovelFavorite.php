<?php
declare(strict_types=1);

namespace app\model;

/**
 * 用户收藏小说关系模型。
 */
class UserNovelFavorite extends BaseModel
{
    protected $table = 'user_novel_favorites';

    /**
     * 仅记录创建时间。
     * @var string|false
     */
    protected $updateTime = false;
}
