<?php

namespace App\Infrastructure\Services;


use App\Domain\DTO\TransactionDTO;
use App\Domain\Entity\Transaction;
use App\Domain\Enum\TransactionAuthorization;
use App\Domain\Enum\UserRoles;
use App\Domain\Exception\PayeeIsCommunException;
use App\Domain\Exception\PayerHasNotBalanceLimit;
use App\Domain\Exception\PayerIsNotCommunException;
use App\Domain\Repository\UserRepository;
use App\Domain\Repository\WalletRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class TransactionService
{
    public function __construct(
        private HttpClientInterface $client,
        private UserRepository $userRepository,
        private WalletRepository $walletRepository
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function create(TransactionDTO $transactionDTO, UserInterface $user): void
    {
        $transaction = $this->builder($transactionDTO);
        $this->validate($transactionDTO, $user);
        $this->getAuthorization();
        $this->debitWallet($transactionDTO, $user);
        $this->creditWallet($transactionDTO, $transaction);
        //TODO notify user
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getAuthorization(): bool
    {
        $response = $this->client->request(
            'GET',
            'https://run.mocky.io/v3/a44f11a6-1788-4160-bc48-610e66f8386b'
        );

        return json_decode(
            $response->getContent(),
            true
            )['message']
            === TransactionAuthorization::Autorizado->value;
    }

    public function builder(TransactionDTO $transactionDTO): Transaction
    {
        $transaction = new Transaction();
        $transaction->setAmount($transactionDTO->getValue());


        return $transaction;
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

    private function validate(TransactionDTO $transactionDTO, UserInterface $user): void
    {
        $payee = $this->userRepository->find($transactionDTO->getPayee());
        $this->payerIsCommun($user->getRoles());
        $this->payeeIsNotCommun($payee->getRoles());
        $this->payerHasBalanceLimit($user->getWallet()->getBalance(), $transactionDTO->getValue());
    }

    private function payerIsCommun(array $roles): void
    {
        if (!in_array(UserRoles::Comun->value, $roles)) {
            throw new PayerIsNotCommunException('O lojista não pode ser o pagador');
        }
    }

    private function payeeIsNotCommun(array $roles): void
    {
        if (!in_array(UserRoles::Logista->value, $roles)) {
            throw new PayeeIsCommunException('Apenas lojistas recebem pagamentos');
        }
    }

    private function payerHasBalanceLimit(float $balance, float $value): void
    {
        if ($balance < $value) {
            throw new PayerHasNotBalanceLimit('O lojista não tem limite para realizar a operação');
        }
    }
}