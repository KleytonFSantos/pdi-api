<?php

namespace App\Domain\DTO;

class TransactionDTO
{
    private float $value;
    private int $payee;

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): void
    {
        $this->value = $value;
    }


    public function getPayee(): int
    {
        return $this->payee;
    }

    public function setPayee(int $payee): void
    {
        $this->payee = $payee;
    }
}