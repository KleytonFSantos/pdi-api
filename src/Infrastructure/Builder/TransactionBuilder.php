<?php

namespace App\Infrastructure\Builder;

use App\Domain\DTO\TransactionDTO;
use App\Domain\Entity\Transaction;
use Symfony\Component\Security\Core\User\UserInterface;

class TransactionBuilder
{
    public function build(TransactionDTO $transactionDTO, UserInterface $user): Transaction
    {
        $transaction = new Transaction();
        $transaction->setAmount($transactionDTO->getValue());
        $transaction->setPayerId($user->getId());
        $transaction->setPayeeId($transactionDTO->getPayee());

        return $transaction;
    }
}
