<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;
use think\model\relation\HasMany;

/**
 * 评论模型，支持楼中楼层级。
 */
class Comment extends BaseModel
{
    protected $table = 'comments';

    /**
     * 自动维护 created_at / updated_at。
     * @var string|false
     */
    protected $updateTime = 'updated_at';

    /**
     * 可见字段，包含审核来源 review_source。
     * @var array
     */
    protected $visible = [
        'id',
        'novel_id',
        'chapter_id',
        'user_id',
        'parent_id',
        'root_id',
        'content',
        'is_r18',
        'status',
        'review_source',
        'like_count',
        'created_at',
        'updated_at',
    ];

    /**
     * 小说关联。
     */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }

    /**
     * 章节关联。
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id', 'id');
    }

    /**
     * 用户关联。
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 父级评论。
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id', 'id');
    }

    /**
     * 根评论。
     */
    public function root(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'root_id', 'id');
    }

    /**
     * 子评论集合。
     */
    public function children(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id', 'id');
    }
}
