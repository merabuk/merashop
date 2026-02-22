<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Persistence\Doctrine\Repository;

use App\Customer\Domain\Entity\CustomerProfile;
use App\Customer\Domain\Exception\InvalidCustomerValueObjectException;
use App\Customer\Domain\Repository\CustomerProfileReadRepositoryInterface;
use App\Customer\Infrastructure\Persistence\Doctrine\Entity\OrmCustomerProfile;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\ReadRepositoryTrait;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid as SymfonyUlid;

class CustomerProfileReadRepository extends BaseCustomerProfileRepository implements CustomerProfileReadRepositoryInterface
{
    use ReadRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws InvalidUlidException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCustomerValueObjectException
     */
    public function findById(int $id): ?CustomerProfile
    {
        $ormUser = $this->find($id);

        return $this->checkAndMapToDomain($ormUser);
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidUlidException
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
     * @throws EntityIdMissingException
     * @throws InvalidUlidException
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
