<?php
declare(strict_types=1);

namespace app\model;

/**
 * 域名列表模型，用于管理对外域名。
 */
class Domain extends BaseModel
{
    protected $table = 'domain_list';

    /**
     * 仅记录创建时间。
     * @var string|false
     */
    protected $updateTime = false;
}
