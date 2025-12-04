<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;
use think\model\relation\HasOne;

/**
 * 章节模型，关联小说与正文内容。
 */
class Chapter extends BaseModel
{
    protected $table = 'chapters';

    /**
     * 章节仅记录创建时间。
     * @var string|false
     */
    protected $updateTime = false;

    /**
     * 小说关联。
     */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }

    /**
     * 正文内容关联。
     */
    public function content(): HasOne
    {
        return $this->hasOne(ChapterContent::class, 'chapter_id', 'id');
    }
}
