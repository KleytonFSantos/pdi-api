<?php

namespace App\Domain\Exception;

class PayerIsNotCommunException extends \DomainException
{
    public function __construct(string $message = '', int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
