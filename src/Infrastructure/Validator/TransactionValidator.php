<?php

namespace App\Infrastructure\Validator;

use App\Domain\DTO\TransactionDTO;
use App\Domain\Enum\UserRoles;
use App\Domain\Exception\PayeeIsCommunException;
use App\Domain\Exception\PayerHasNotBalanceLimit;
use App\Domain\Exception\PayerIsNotCommunException;
use App\Domain\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class TransactionValidator
{
    public function __construct(
        private readonly UserRepository $userRepository,
    )
    {
    }

    public function validate(TransactionDTO $transactionDTO, UserInterface $user): void
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