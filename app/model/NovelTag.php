<?php
declare(strict_types=1);

namespace app\model;

/**
 * 小说与标签的关联表模型。
 */
class NovelTag extends BaseModel
{
    protected $table = 'novel_tags';

    /**
     * 该表无时间戳字段。
     * @var bool|string
     */
    protected $autoWriteTimestamp = false;
}
