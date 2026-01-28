<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper;

use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectExceptionInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientSecretHash;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Id;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\ScopeCollection;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmModuleAccount;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<ModuleAccount, OrmModuleAccount>
 */
class ModuleAccountMapper implements MapperInterface
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmModuleAccount
    {
        $this->assertIsType(ModuleAccount::class, $domain);

        /** @var ModuleAccount $domain */
        $orm = new OrmModuleAccount();

        $orm->setId($domain->getId()?->value());
        $orm->ulid = $domain->getUlid()->value();
        $orm->clientId = $domain->getClientId()->value();
        $orm->clientSecret = $domain->getClientSecret()->value();
        $orm->scopes = $domain->getScopes()->toStrings();

        return $orm;
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidIdentityAccessValueObjectExceptionInterface
     * @throws EntityIdMissingException
     */
    public function fromDoctrineOrm(object $orm): ModuleAccount
    {
        $this->assertIsType(OrmModuleAccount::class, $orm);

        /* @var OrmModuleAccount $orm */
        $id = Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class));

        return new ModuleAccount(
            ulid: Ulid::fromString($orm->ulid),
            clientId: ClientId::fromString($orm->clientId),
            clientSecret: ClientSecretHash::fromString($orm->clientSecret),
            scopes: ScopeCollection::fromStrings($orm->scopes),
            id: $id,
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(ModuleAccount::class, $domain);
        $this->assertIsType(OrmModuleAccount::class, $orm);

        /* @var ModuleAccount $domain */
        /* @var OrmModuleAccount $orm */

        $orm->scopes = $domain->getScopes()->toStrings();
    }
}
