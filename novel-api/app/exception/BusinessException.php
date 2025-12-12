<?php
declare(strict_types=1);

namespace app\exception;

use RuntimeException;

class BusinessException extends RuntimeException
{
    protected $data;

    protected int $httpStatus;

    public function __construct(string $message, int $code = 1, $data = [], int $httpStatus = 200)
    {
        parent::__construct($message, $code);
        $this->data       = $data;
        $this->httpStatus = $httpStatus;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}
