<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO\Contracts;

interface ClientCredentialsInterface extends AccountTypeInterface, CredentialsInterface
{
    public function getClientId(): string;

    public function getClientSecret(): string;
}
