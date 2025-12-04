<?php
declare(strict_types=1);

namespace app\model;

/**
 * 订单模型，覆盖章节/整本/VIP 购买记录。
 */
class Order extends BaseModel
{
    protected $table = 'orders';

    /**
     * JSON 自动转换字段。
     * @var array
     */
    protected $json = ['extra'];
}
