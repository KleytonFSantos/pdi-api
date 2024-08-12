<?php

namespace App\Infrastructure\Validator;

use App\Domain\DTO\TransactionDTO;
use App\Domain\Enum\TransactionAuthorization;
use App\Domain\Enum\UserRoles;
use App\Domain\Exception\PayeeIsCommunException;
use App\Domain\Exception\PayerHasNotBalanceLimit;
use App\Domain\Exception\PayerIsNotCommunException;
use App\Domain\Repository\UserRepository;
use App\Infrastructure\Client\TransactionAuthorizationClient;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class TransactionValidator
{
    public function __construct(
        private UserRepository $userRepository,
        private TransactionAuthorizationClient $transactionAuthorizationClient,
    ) {
    }

    public function validate(TransactionDTO $transactionDTO, UserInterface $user): void
    {
        $payee = $this->userRepository->find($transactionDTO->getPayee());
        $this->payerIsCommun($user->getRoles());
        $this->payeeIsNotCommun($payee->getRoles());
        $this->payerHasBalanceLimit($user->getWallet()->getBalance(), $transactionDTO->getValue());
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function checkAuthorizationStatus(): bool
    {
        return $this->transactionAuthorizationClient->authorizationStatus()
            === TransactionAuthorization::Autorizado->value;
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