<?php
declare(strict_types=1);

namespace app\model;

class PaymentChannel extends BaseModel
{
    protected $name = 'payment_channel';
    protected $pk = 'id';
    protected $json = ['pay_rules'];
}
