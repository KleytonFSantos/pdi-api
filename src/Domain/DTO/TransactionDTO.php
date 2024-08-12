<?php

namespace App\Domain\DTO;

use OpenApi\Attributes\Property;
use Symfony\Component\Serializer\Attribute\Groups;

class TransactionDTO
{
    #[Groups(['create'])]
    #[Property(type: 'float')]
    private float $value;
    #[Groups(['create'])]
    #[Property(type: 'number')]
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
