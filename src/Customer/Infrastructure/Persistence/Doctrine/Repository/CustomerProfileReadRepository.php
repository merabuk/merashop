<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Persistence\Doctrine\Repository;

use App\Customer\Domain\Entity\CustomerProfile;
use App\Customer\Domain\Exception\CustomerProfile\CustomerProfileNotFoundException;
use App\Customer\Domain\Exception\InvalidCustomerValueObjectException;
use App\Customer\Domain\Repository\CustomerProfileReadRepositoryInterface;
use App\Customer\Domain\ValueObject\CustomerProfile\Id;
use App\Customer\Infrastructure\Persistence\Doctrine\Entity\OrmCustomerProfile;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\ValueObject\Identity\Ulid;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\ReadRepositoryTrait;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid as SymfonyUlid;

class CustomerProfileReadRepository extends BaseCustomerProfileRepository implements CustomerProfileReadRepositoryInterface
{
    use ReadRepositoryTrait;

    /**
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCustomerValueObjectException
     */
    public function findById(Id $id): ?CustomerProfile
    {
        $ormUser = $this->find($id->value());

        return $this->checkAndMapToDomain($ormUser);
    }

    /**
     * @throws CustomerProfileNotFoundException
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCustomerValueObjectException
     */
    public function getByUlid(Ulid $ulid): CustomerProfile
    {
        return $this->findByUlid($ulid) ?? throw new CustomerProfileNotFoundException();
    }

    /**
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCustomerValueObjectException
     */
    public function findByUlid(Ulid $ulid): ?CustomerProfile
    {
        $ormUser = $this->findOneBy(['userUlid' => SymfonyUlid::fromString($ulid->value())]);

        return $this->checkAndMapToDomain($ormUser);
    }

    public function existsByUlid(Ulid $ulid): bool
    {
        return $this->_existsBy([
            $this->_makeCriterion(field: 'userUlid', value: $ulid->value(), type: UlidType::NAME),
        ]);
    }

    /**
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCustomerValueObjectException
     */
    private function checkAndMapToDomain(?object $ormUser): ?CustomerProfile
    {
        if (false === $ormUser instanceof OrmCustomerProfile) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($ormUser);
    }
}
