<?php

namespace App\Infrastructure\Services;


use App\Domain\DTO\TransactionDTO;
use App\Domain\Entity\Transaction;
use App\Domain\Repository\UserRepository;
use App\Domain\Repository\WalletRepository;
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
        private UserRepository $userRepository,
        private WalletRepository $walletRepository
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

        $this->debitWallet($transactionDTO, $user);
        $this->creditWallet($transactionDTO, $transaction);
        //TODO notify user
    }

    private function debitWallet(TransactionDTO $transactionDTO, UserInterface $user): void
    {
        $user->getWallet()->setBalance($user->getWallet()->getBalance() - $transactionDTO->getValue());
        $user->setWallet($user->getWallet());
        $this->walletRepository->save($user->getWallet(), true);
    }

    private function creditWallet(TransactionDTO $transactionDTO, Transaction $transaction): void
    {
        $payee = $this->userRepository->find($transactionDTO->getPayee());
        $payee->getWallet()->setBalance($payee->getWallet()->getBalance() + $transactionDTO->getValue());
        $payee->setWallet($payee->getWallet());
        $this->walletRepository->save($payee->getWallet(), true);
    }
}