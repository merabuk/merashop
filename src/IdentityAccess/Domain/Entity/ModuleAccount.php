<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Entity;

use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\PasswordHash;
use App\IdentityAccess\Domain\ValueObject\ScopeCollection;
use App\IdentityAccess\Domain\ValueObject\Ulid;

class ModuleAccount
{
    public function __construct(
        private readonly Ulid $ulid,
        private ClientId $clientId,
        private PasswordHash $clientSecret,
        private ScopeCollection $scopes,
    ) {
    }

    public function getUlid(): Ulid
    {
        return $this->ulid;
    }

    public function getClientId(): ClientId
    {
        return $this->clientId;
    }

    public function getClientSecret(): PasswordHash
    {
        return $this->clientSecret;
    }

    public function getScopes(): ScopeCollection
    {
        return $this->scopes;
    }

    public function updateClientId(ClientId $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function updateClientSecret(PasswordHash $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    public function updateScopes(ScopeCollection $scopes): void
    {
        $this->scopes = $scopes;
    }
}
