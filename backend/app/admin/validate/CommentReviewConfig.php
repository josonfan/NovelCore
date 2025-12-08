<?php
namespace app\admin\validate;

use think\Validate;

class CommentReviewConfig extends Validate
{
    protected $scene = [
        'create' => ['site_id','enabled','require_approval','max_length','max_per_minute','forbidden_words_json'],
        'update' => ['site_id','enabled','require_approval','max_length','max_per_minute','forbidden_words_json','id'],
    ];
    protected $rule = [
        'id' => 'number',
        'site_id' => 'number',
        'enabled' => 'in:0,1',
        'require_approval' => 'in:0,1',
        'max_length' => 'number',
        'max_per_minute' => 'number',
        'forbidden_words_json' => 'chsDash',
    ];
}

