<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\AdminAccount;
use App\IdentityAccess\Domain\Repository\AdminAccountWriteRepositoryInterface;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmAdminAccount;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class AdminAccountWriteRepository extends BaseAdminAccountRepository implements AdminAccountWriteRepositoryInterface
{
    /**
     * @use WriteRepositoryTrait<AdminAccount, OrmAdminAccount>
     */
    use WriteRepositoryTrait;

    /**
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     * @throws ValueObjectExceptionInterface
     */
    public function save(AdminAccount $adminAccount): AdminAccount
    {
        $orm = $this->_save(domain: $adminAccount, id: $adminAccount->getId()?->value());

        return $this->mapper->fromDoctrineOrm($orm);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(AdminAccount $adminAccount): void
    {
        $this->_delete($adminAccount->getId()?->value());
    }
}
