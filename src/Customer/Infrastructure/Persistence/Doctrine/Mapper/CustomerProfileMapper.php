<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Persistence\Doctrine\Mapper;

use App\Customer\Domain\Entity\CustomerProfile;
use App\Customer\Domain\Exception\InvalidCustomerValueObjectException;
use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use App\Customer\Domain\ValueObject\CustomerProfile\Id;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use App\Customer\Domain\ValueObject\CustomerProfile\PhoneNumber;
use App\Customer\Infrastructure\Persistence\Doctrine\Entity\OrmCustomerProfile;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<CustomerProfile, OrmCustomerProfile>
 */
class CustomerProfileMapper implements MapperInterface
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmCustomerProfile
    {
        $this->assertIsType(CustomerProfile::class, $domain);

        /** @var CustomerProfile $domain */
        $orm = new OrmCustomerProfile();

        $orm->setId($domain->getId()?->value());
        $orm->userUlid = $domain->getUserUlid()->value();
        $orm->firstName = $domain->getFirstName()?->value();
        $orm->lastName = $domain->getLastName()?->value();
        $orm->phoneNumber = $domain->getPhoneNumber()?->value();

        return $orm;
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCustomerValueObjectException
     * @throws InvalidUlidException
     */
    public function fromDoctrineOrm(object $orm): CustomerProfile
    {
        $this->assertIsType(OrmCustomerProfile::class, $orm);

        /* @var OrmCustomerProfile $orm */
        $id = Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class));

        return new CustomerProfile(
            userUlid: Ulid::fromString($orm->userUlid),
            firstName: $orm->firstName ? FirstName::fromString($orm->firstName) : null,
            lastName: $orm->lastName ? LastName::fromString($orm->lastName) : null,
            phoneNumber: $orm->phoneNumber ? PhoneNumber::fromString($orm->phoneNumber) : null,
            id: $id,
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(CustomerProfile::class, $domain);
        $this->assertIsType(OrmCustomerProfile::class, $orm);

        /* @var CustomerProfile $domain */
        /* @var OrmCustomerProfile $orm */
        $orm->firstName = $domain->getFirstName()?->value();
        $orm->lastName = $domain->getLastName()?->value();
        $orm->phoneNumber = $domain->getPhoneNumber()?->value();
    }
}
