<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Persistence\Doctrine\Repository;

use App\Customer\Domain\Entity\CustomerProfile;
use App\Customer\Domain\Repository\CustomerProfileWriteRepositoryInterface;
use App\Customer\Infrastructure\Persistence\Doctrine\Entity\OrmCustomerProfile;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class CustomerProfileWriteRepository extends BaseCustomerProfileRepository implements CustomerProfileWriteRepositoryInterface
{
    /**
     * @use WriteRepositoryTrait<CustomerProfile, OrmCustomerProfile>
     */
    use WriteRepositoryTrait;

    /**
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     * @throws ValueObjectExceptionInterface
     */
    public function save(CustomerProfile $customerProfile): CustomerProfile
    {
        $orm = $this->_save(domain: $customerProfile, id: $customerProfile->getId()?->value());

        return $this->mapper->fromDoctrineOrm($orm);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(CustomerProfile $customerProfile): void
    {
        $this->_delete($customerProfile->getId()?->value());
    }
}
