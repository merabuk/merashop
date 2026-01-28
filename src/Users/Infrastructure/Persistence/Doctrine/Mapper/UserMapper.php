<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Mapper;

use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;
use App\Users\Domain\Entity\User;
use App\Users\Domain\Exception\InvalidUserValueObjectExceptionInterface;
use App\Users\Domain\ValueObject\EmailAddress;
use App\Users\Domain\ValueObject\FirstName;
use App\Users\Domain\ValueObject\Id;
use App\Users\Domain\ValueObject\LastName;
use App\Users\Domain\ValueObject\PasswordHash;
use App\Users\Domain\ValueObject\PhoneNumber;
use App\Users\Domain\ValueObject\Ulid;
use App\Users\Infrastructure\Persistence\Doctrine\Entity\OrmUser;

/**
 * @implements MapperInterface<User, OrmUser>
 */
class UserMapper implements MapperInterface
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmUser
    {
        $this->assertIsType(User::class, $domain);

        /** @var User $domain */
        $orm = new OrmUser();

        $orm->setId($domain->getId()?->value());
        $orm->ulid = $domain->getUlid()->value();
        $orm->firstName = $domain->getFirstName()->value();
        $orm->lastName = $domain->getLastName()->value();
        $orm->email = $domain->getEmail()->value();
        $orm->phoneNumber = $domain->getPhoneNumber()?->value();
        $orm->password = $domain->getPassword()->value();

        return $orm;
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidUserValueObjectExceptionInterface
     * @throws EntityIdMissingException
     */
    public function fromDoctrineOrm(object $orm): User
    {
        $this->assertIsType(OrmUser::class, $orm);

        /* @var OrmUser $orm */

        return new User(
            id: Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class)),
            ulid: Ulid::fromString($orm->ulid),
            email: EmailAddress::fromString($orm->email),
            firstName: FirstName::fromString($orm->firstName),
            lastName: LastName::fromString($orm->lastName),
            phoneNumber: PhoneNumber::fromString($orm->phoneNumber),
            password: PasswordHash::fromString($orm->password),
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(User::class, $domain);
        $this->assertIsType(OrmUser::class, $orm);

        $orm->firstName = $domain->getFirstName()->value();
        $orm->lastName = $domain->getLastName()->value();
        $orm->phoneNumber = $domain->getPhoneNumber()?->value();
    }
}
