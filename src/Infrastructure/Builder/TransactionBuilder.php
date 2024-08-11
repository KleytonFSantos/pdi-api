<?php

namespace App\Infrastructure\Builder;

use App\Domain\DTO\TransactionDTO;
use App\Domain\Entity\Transaction;

class TransactionBuilder
{
    public function build(TransactionDTO $transactionDTO): Transaction
    {
        $transaction = new Transaction();
        $transaction->setAmount($transactionDTO->getValue());

        return $transaction;
    }
}