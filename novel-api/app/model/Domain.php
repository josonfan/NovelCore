<?php
declare(strict_types=1);

namespace app\model;

/**
 * 域名列表模型，用于管理对外域名。
 */
class Domain extends BaseModel
{
    protected $name = 'domain_list';
    protected $pk = 'id';

    /**
     * 仅记录创建时间。
     * @var string|false
     */
    protected $updateTime = false;
}
