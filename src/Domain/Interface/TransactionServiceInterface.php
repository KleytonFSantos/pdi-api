<?php

namespace App\Domain\Interface;

use App\Domain\DTO\TransactionDTO;
use Symfony\Component\Security\Core\User\UserInterface;

interface TransactionServiceInterface
{
    public function create(TransactionDTO $transactionDTO, UserInterface $payer): void;

    public function getTransactionHistoryByUser(int $userId): array;
}
