<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

interface RefreshTokenInterface extends CredentialsInterface
{
    public function getRefreshToken(): string;
}
