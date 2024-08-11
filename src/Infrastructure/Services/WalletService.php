<?php

namespace App\Infrastructure\Services;

use App\Domain\DTO\TransactionDTO;
use App\Domain\Entity\Transaction;
use App\Domain\Entity\User;
use App\Domain\Entity\Wallet;
use App\Domain\Repository\UserRepository;
use App\Domain\Repository\WalletRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class WalletService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly WalletRepository $walletRepository,
    )
    {
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
        $user->getWallet()->setBalance($user->getWallet()->getBalance() - $transactionDTO->getValue());
        $user->setWallet($user->getWallet());
        $this->walletRepository->save($user->getWallet(), true);
    }

    public function creditWallet(TransactionDTO $transactionDTO, Transaction $transaction): void
    {
        $payee = $this->userRepository->find($transactionDTO->getPayee());
        $payee->getWallet()->setBalance($payee->getWallet()->getBalance() + $transactionDTO->getValue());
        $payee->setWallet($payee->getWallet());
        $this->walletRepository->save($payee->getWallet(), true);
    }
}