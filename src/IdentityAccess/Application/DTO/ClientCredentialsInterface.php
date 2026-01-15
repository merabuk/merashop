<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

interface ClientCredentialsInterface extends CredentialsInterface
{
    public function getClientId(): string;

    public function getClientSecret(): string;
}
