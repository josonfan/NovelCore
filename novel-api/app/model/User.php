<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\HasMany;

/**
 * 用户模型，承载作者与读者基础信息。
 *
 * 如需支持邮箱登录/注册，推荐在数据库执行：
 * ALTER TABLE `users` ADD COLUMN `email` VARCHAR(128) NULL COMMENT '邮箱';
 */
class User extends BaseModel
{
    protected $pk = 'id';
    /**
     * 关联表名。
     * @var string
     */
    protected $name = 'users';

    /**
     * 作者的小说列表。
     */
    public function novels(): HasMany
    {
        return $this->hasMany(Novel::class, 'author_id', 'id');
    }

    /**
     * 收藏关系集合。
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(UserNovelFavorite::class, 'user_id', 'id');
    }

    /**
     * 点赞关系集合。
     */
    public function likes(): HasMany
    {
        return $this->hasMany(UserNovelLike::class, 'user_id', 'id');
    }
}
