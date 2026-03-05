<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Factory;

use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountClientIdException;
use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountPasswordHashException;
use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountUlidException;
use App\IdentityAccess\Domain\Exception\ValueObject\InvalidScopeException;
use App\IdentityAccess\Domain\Factory\Contract\ModuleAccountFactoryInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientSecretHash;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\ScopeCollection;

final readonly class ModuleAccountFactory implements ModuleAccountFactoryInterface
{
    /**
     * @param string[] $scopes
     *
     * @throws InvalidModuleAccountClientIdException
     * @throws InvalidModuleAccountPasswordHashException
     * @throws InvalidModuleAccountUlidException
     * @throws InvalidScopeException
     */
    public function createForTest(
        string $ulid,
        string $clientId,
        string $clientSecretHash,
        array $scopes,
    ): ModuleAccount {
        return ModuleAccount::create(
            ulid: Ulid::fromString($ulid),
            clientId: ClientId::fromString($clientId),
            clientSecret: ClientSecretHash::fromString($clientSecretHash),
            scopes: ScopeCollection::fromStrings($scopes),
        );
    }
}
