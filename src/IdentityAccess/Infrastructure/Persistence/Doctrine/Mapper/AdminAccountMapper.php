<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper;

use App\IdentityAccess\Domain\Entity\AdminAccount;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Id;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordChangedAt;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordHash;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Status;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmAdminAccount;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<AdminAccount, OrmAdminAccount>
 */
class AdminAccountMapper implements MapperInterface
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmAdminAccount
    {
        $this->assertIsType(AdminAccount::class, $domain);
        /** @var AdminAccount $domain */
        $orm = new OrmAdminAccount();

        $orm->setId($domain->getId()?->value());
        $orm->ulid = $domain->getUlid()->value();
        $orm->email = $domain->getEmail()->value();
        $orm->passwordHash = $domain->getPasswordHash()->value();
        $orm->roles = $domain->getRoles()->toStrings();
        $orm->status = $domain->getStatus()->value();
        $orm->passwordChangedAt = $domain->getPasswordChangedAt()?->value();

        return $orm;
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidIdentityAccessValueObjectException
     */
    public function fromDoctrineOrm(object $orm): AdminAccount
    {
        $this->assertIsType(OrmAdminAccount::class, $orm);
        /* @var OrmAdminAccount $orm */

        $id = Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class));

        return new AdminAccount(
            ulid: Ulid::fromString($orm->ulid),
            email: EmailAddress::fromString($orm->email),
            passwordHash: PasswordHash::fromString($orm->passwordHash),
            roles: RoleCollection::fromStrings($orm->roles),
            status: Status::fromEnum($orm->status),
            passwordChangedAt: $orm->passwordChangedAt ? PasswordChangedAt::fromDateTime($orm->passwordChangedAt) : null,
            id: $id,
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(AdminAccount::class, $domain);
        $this->assertIsType(OrmAdminAccount::class, $orm);
        /* @var AdminAccount $domain */
        /* @var OrmAdminAccount $orm */

        $orm->passwordHash = $domain->getPasswordHash()->value();
        $orm->roles = $domain->getRoles()->toStrings();
        $orm->status = $domain->getStatus()->value();
        $orm->passwordChangedAt = $domain->getPasswordChangedAt()?->value();
    }
}
