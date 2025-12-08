<?php
namespace app\admin\validate;

use think\Validate;

class Novel extends Validate
{
    protected $scene = [
        'create' => ['title','category_id','status','is_r18','is_vip'],
        'update' => ['title','category_id','status','is_r18','is_vip'],
    ];
    protected $rule = [
        'title' => 'require',
        'category_id' => 'require|number|>:0',
        'status' => 'in:0,1',
        'is_r18' => 'in:0,1',
        'is_vip' => 'in:0,1',
        'slug' => 'alphaDash|unique:novels',
    ];
    protected $message = [
        'title.require' => '小说标题不能为空',
        'category_id.require' => '分类不能为空',
        'category_id.number' => '分类ID必须为数字',
        'category_id.>' => '分类ID必须大于0',
        'status.in' => '状态仅支持 0 或 1',
        'is_r18.in' => 'is_r18 仅支持 0 或 1',
        'is_vip.in' => 'is_vip 仅支持 0 或 1',
        'slug.alphaDash' => '短链只允许字母数字下划线和破折号',
        'slug.unique' => '短链已存在',
    ];
}

