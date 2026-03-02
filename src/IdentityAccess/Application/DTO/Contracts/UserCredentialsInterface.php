<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO\Contracts;

interface UserCredentialsInterface extends AccountTypeInterface, CredentialsInterface
{
    public function getUsername(): string;

    public function getPassword(): string;
}
