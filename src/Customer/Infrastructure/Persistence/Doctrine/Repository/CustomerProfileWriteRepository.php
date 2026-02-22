<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Persistence\Doctrine\Repository;

use App\Customer\Domain\Entity\CustomerProfile;
use App\Customer\Domain\Repository\CustomerProfileWriteRepositoryInterface;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;

class CustomerProfileWriteRepository extends BaseCustomerProfileRepository implements CustomerProfileWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws EntityIdMissingException
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
     * @throws IncompatibleMappedEntityException
     */
    public function delete(CustomerProfile $customerProfile): void
    {
        $this->_delete($customerProfile);
    }
}
