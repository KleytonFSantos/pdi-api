<?php

namespace App\Domain\Interface;

use App\Domain\Entity\User;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface RegisterServiceInterface
{
    public function hashPassword(string $plainTextPassword): string;
    public function validateUser(User $user): ?ConstraintViolationListInterface;

}