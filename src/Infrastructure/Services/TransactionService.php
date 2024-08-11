<?php

namespace App\Infrastructure\Services;


use App\Domain\DTO\TransactionDTO;
use App\Infrastructure\Builder\TransactionBuilder;
use App\Infrastructure\Client\TransactionAuthorizationClient;
use App\Infrastructure\Validator\TransactionValidator;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class TransactionService
{
    public function __construct(
        private TransactionAuthorizationClient $authorizationClient,
        private TransactionBuilder $transactionBuilder,
        private TransactionValidator $validator,
        private WalletService $walletService
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function create(TransactionDTO $transactionDTO, UserInterface $user): void
    {
        $transaction = $this->transactionBuilder->build($transactionDTO);
        $this->validator->validate($transactionDTO, $user);

        if (! $this->authorizationClient->checkAuthorizationStatus()) {
            throw new Exception('Payment was not authorized');
        }

        $this->walletService->debitWallet($transactionDTO, $user);
        $this->walletService->creditWallet($transactionDTO, $transaction);
        //TODO notify user
    }
}