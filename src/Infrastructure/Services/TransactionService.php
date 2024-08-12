<?php

namespace App\Infrastructure\Services;


use App\Domain\DTO\TransactionDTO;
use App\Domain\Interface\TransactionServiceInterface;
use App\Domain\Repository\TransactionRepository;
use App\Infrastructure\Builder\TransactionBuilder;
use App\Infrastructure\Validator\TransactionValidator;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        private TransactionBuilder             $transactionBuilder,
        private TransactionRepository          $transactionRepository,
        private TransactionValidator           $validator,
        private WalletService                  $walletService,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function create(TransactionDTO $transactionDTO, UserInterface $payer): void
    {
        $this->validator->validate($transactionDTO, $payer);
        $transaction = $this->transactionBuilder->build($transactionDTO, $payer);

        if (! $this->validator->checkAuthorizationStatus()) {
            throw new Exception('Payment was not authorized');
        }

        $this->transactionRepository->save($transaction);
        $this->walletService->debitWallet($transactionDTO, $payer);
        $this->walletService->creditWallet($transactionDTO, $transaction);
        //TODO notify user
    }

    public function getTransactionHistoryByUser(int $userId): array
    {
        return $this->transactionRepository->getTransactionHistoryByUser($userId);
    }
}