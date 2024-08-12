<?php

namespace App\Infrastructure\Services;

use App\Domain\DTO\TransactionDTO;
use App\Domain\Entity\User;
use App\Domain\Entity\Wallet;
use App\Domain\Repository\UserRepository;
use App\Domain\Repository\WalletRepository;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class WalletService
{
    public function __construct(
        private UserRepository $userRepository,
        private WalletRepository $walletRepository,
    ) {
    }

    public function create(User $user): Wallet
    {
        $wallet = new Wallet();
        $wallet->setBalance(0);
        $wallet->setUser($user);

        return $wallet;
    }

    public function debitWallet(TransactionDTO $transactionDTO, UserInterface $user): void
    {
        $wallet = $user->getWallet();
        $wallet->setBalance($wallet->getBalance() - $transactionDTO->getValue());
        $this->walletRepository->save($wallet);
    }

    public function creditWallet(TransactionDTO $transactionDTO): void
    {
        $payee = $this->userRepository->find($transactionDTO->getPayee());
        $wallet = $payee->getWallet();
        $wallet->setBalance($wallet->getBalance() + $transactionDTO->getValue());
        $this->walletRepository->save($wallet);
    }
}
