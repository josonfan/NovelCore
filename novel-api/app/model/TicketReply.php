<?php
declare(strict_types=1);

namespace app\model;

class TicketReply extends CacheModel
{
    protected $name = 'ticket_replies';
    protected $pk = 'id';
}
