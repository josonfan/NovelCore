<?php
namespace app\admin\validate;

use think\Validate;

class Chapter extends Validate
{
    protected $scene = [
        'create' => ['novel_id','title','is_free','is_vip','price','sort_order'],
        'update' => ['title','is_free','is_vip','price','sort_order'],
    ];
    protected $rule = [
        'novel_id' => 'require|number|>:0',
        'title' => 'require',
        'is_free' => 'in:0,1',
        'is_vip' => 'in:0,1',
        'price' => 'number',
        'sort_order' => 'number',
        'chapter_uuid' => 'alphaDash|unique:chapters',
    ];
    protected $message = [
        'novel_id.require' => '小说ID不能为空',
        'novel_id.number' => '小说ID必须为数字',
        'novel_id.>' => '小说ID必须大于0',
        'title.require' => '章节标题不能为空',
        'is_free.in' => 'is_free 仅支持 0 或 1',
        'is_vip.in' => 'is_vip 仅支持 0 或 1',
        'price.number' => '价格必须为数字',
        'sort_order.number' => '排序必须为数字',
        'chapter_uuid.alphaDash' => '章节UUID只允许字母数字下划线和破折号',
        'chapter_uuid.unique' => '章节UUID已存在',
    ];
}

