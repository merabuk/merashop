<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Context;

use App\IdentityAccess\Application\Security\CurrentAccessTokenContextInterface;
use RuntimeException;
use Symfony\Contracts\Service\ResetInterface;

class CurrentAccessTokenContext implements CurrentAccessTokenContextInterface, ResetInterface
{
    private ?string $jti = null;
    private ?int $expiresAt = null;

    public function set(string $jti, int $expiresAt): void
    {
        $this->jti = $jti;
        $this->expiresAt = $expiresAt;
    }

    public function getJti(): string
    {
        return $this->jti ?? throw $this->makeException('jti');
    }

    public function getExpiresAt(): int
    {
        return $this->expiresAt ?? throw $this->makeException('expiresAt');
    }

    public function reset(): void
    {
        $this->jti = null;
        $this->expiresAt = null;
    }

    private function makeException(string $property): RuntimeException
    {
        return new RuntimeException(sprintf('%s::$%s property must be set before getting', self::class, $property));
    }
}
