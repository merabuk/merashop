<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper;

use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Id;
use App\IdentityAccess\Domain\ValueObject\UserAccount\PasswordHash;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Ulid;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmUserAccount;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<UserAccount, OrmUserAccount>
 */
final readonly class UserAccountMapper implements MapperInterface
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmUserAccount
    {
        $this->assertIsType(UserAccount::class, $domain);
        /** @var UserAccount $domain */
        $orm = new OrmUserAccount();

        $orm->setId($domain->getId()?->value());
        $orm->ulid = $domain->getUlid()->value();
        $orm->email = $domain->getEmail()->value();
        $orm->passwordHash = $domain->getPasswordHash()->value();
        $orm->roles = $domain->getRoles()->toStrings();

        return $orm;
    }

    /**
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidIdentityAccessValueObjectException
     */
    public function fromDoctrineOrm(object $orm): UserAccount
    {
        $this->assertIsType(OrmUserAccount::class, $orm);
        /* @var OrmUserAccount $orm */

        $id = Id::fromInt($orm->id ?? throw EntityFieldMissingException::forEntityId(className: $orm::class));

        return new UserAccount(
            ulid: Ulid::fromString($orm->ulid ?? throw EntityFieldMissingException::forField(field: 'ulid', className: $orm::class)),
            email: EmailAddress::fromString($orm->email ?? throw EntityFieldMissingException::forField(field: 'email', className: $orm::class)),
            passwordHash: PasswordHash::fromString($orm->passwordHash ?? throw EntityFieldMissingException::forField(field: 'passwordHash', className: $orm::class)),
            roles: RoleCollection::fromStrings($orm->roles),
            id: $id,
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(UserAccount::class, $domain);
        $this->assertIsType(OrmUserAccount::class, $orm);
        /* @var UserAccount $domain */
        /* @var OrmUserAccount $orm */

        $orm->roles = $domain->getRoles()->toStrings();
    }
}
