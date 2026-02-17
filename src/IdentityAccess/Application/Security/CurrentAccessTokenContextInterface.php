<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security;

interface CurrentAccessTokenContextInterface
{
    public function set(string $jti, int $expiresAt): void;

    public function getJti(): string;

    public function getExpiresAt(): int;
}
