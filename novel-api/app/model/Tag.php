<?php
declare(strict_types=1);

namespace app\model;

/**
 * 标签模型，用于主题、剧情、角色等标签。
 */
class Tag extends BaseModel
{
    protected $name = 'tags';
    protected $pk = 'id';

    /**
     * 标签仅有创建时间。
     * @var string|false
     */
    protected $updateTime = false;
}
