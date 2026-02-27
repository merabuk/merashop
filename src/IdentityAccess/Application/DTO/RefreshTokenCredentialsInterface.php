<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

interface RefreshTokenCredentialsInterface extends CredentialsInterface
{
    public function getRefreshToken(): string;
}
