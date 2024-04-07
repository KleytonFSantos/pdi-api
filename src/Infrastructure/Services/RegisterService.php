<?php

namespace App\Infrastructure\Services;

use App\Domain\Entity\User;
use App\Domain\Interface\RegisterServiceInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class RegisterService implements RegisterServiceInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
    ) {
    }

    public function hashPassword(string $plainTextPassword): string
    {
        $user = new User();

        return $this->passwordHasher->hashPassword(
            $user,
            $plainTextPassword
        );
    }

    public function validateUser(User $user): ?ConstraintViolationListInterface
    {
        $errors = $this->validator->validate($user);

        if ($errors->count() > 0) {
            return $errors;
        }

        return null;
    }
}