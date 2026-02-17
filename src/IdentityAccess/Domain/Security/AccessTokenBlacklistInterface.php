<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Security;

interface AccessTokenBlacklistInterface
{
    public function revoke(string $jti, int $expiresAt): void;

    public function isRevoked(string $jti): bool;
}
