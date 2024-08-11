<?php

namespace App\Infrastructure\Services;

use App\Domain\Entity\User;
use App\Domain\Entity\Wallet;

class WalletService
{

    public function create(User $user): Wallet
    {
        $wallet = new Wallet();
        $wallet->setBalance(0);
        $wallet->setUser($user);

        return $wallet;
    }
}