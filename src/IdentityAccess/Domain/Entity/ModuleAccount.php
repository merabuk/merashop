<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Entity;

use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientSecretHash;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Id;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\ScopeCollection;

class ModuleAccount
{
    public function __construct(
        private readonly Ulid $ulid,
        private ClientId $clientId,
        private ClientSecretHash $clientSecret,
        private ScopeCollection $scopes,
        private readonly ?Id $id = null,
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

    public function getClientSecret(): ClientSecretHash
    {
        return $this->clientSecret;
    }

    public function getScopes(): ScopeCollection
    {
        return $this->scopes;
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    public function updateClientId(ClientId $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function updateClientSecret(ClientSecretHash $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    public function updateScopes(ScopeCollection $scopes): void
    {
        $this->scopes = $scopes;
    }
}
