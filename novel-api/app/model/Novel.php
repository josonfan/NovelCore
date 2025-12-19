<?php
declare(strict_types=1);

namespace app\model;

use think\model\relation\BelongsTo;
use think\model\relation\BelongsToMany;
use think\model\relation\HasMany;

/**
 * 小说模型，包含作者、分类、标签与章节关系。
 */
class Novel extends BaseModel
{
    protected $name = 'novels';
    protected $pk = 'id';

    /**
     * JSON 自动转换字段。
     * @var array
     */
    protected $json = ['tags_json'];
    
    /**
     * 作者关联。
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    /**
     * 分类关联。
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    /**
     * 标签多对多关联，经由 novel_tags。
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'bl_novel_tags', 'tag_id', 'novel_id');
    }

    /**
     * 章节列表。
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'novel_id', 'id');
    }
}
