<?php
declare(strict_types=1);

namespace app\model;

/**
 * 分类模型，用于归类小说。
 */
class Category extends BaseModel
{
    protected $table = 'categories';

    /**
     * 分类只写入创建时间，无更新字段。
     * @var string|false
     */
    protected $updateTime = false;
}
