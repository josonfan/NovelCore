<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;

/**
 * 章节正文模型，按章节主键存储长文本。
 */
class ChapterContent extends BaseModel
{
    protected $table = 'chapter_contents';

    /**
     * 主键即 chapter_id。
     * @var string
     */
    protected $pk = 'chapter_id';

    /**
     * 无时间戳字段。
     * @var bool|string
     */
    protected $autoWriteTimestamp = false;

    /**
     * 章节反向关联。
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id', 'id');
    }
}
