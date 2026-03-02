<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO\Contracts;

interface RefreshTokenCredentialsInterface extends CredentialsInterface
{
    public function getRefreshToken(): string;
}
