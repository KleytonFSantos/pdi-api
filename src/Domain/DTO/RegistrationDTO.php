<?php

namespace App\Domain\DTO;

class RegistrationDTO
{
    private ?string $email = null;
    private ?string $name = null;
    private ?string $cpf = null;
    private ?array $roles = [];
    private ?string $password = null;
    private ?string $confirmPassword = null;
}