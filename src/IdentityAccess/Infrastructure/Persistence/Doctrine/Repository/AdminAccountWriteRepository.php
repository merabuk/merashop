<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\AdminAccount;
use App\IdentityAccess\Domain\Repository\AdminAccountWriteRepositoryInterface;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;

final class AdminAccountWriteRepository extends BaseAdminAccountRepository implements AdminAccountWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws EntityIdMissingException
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
     * @throws IncompatibleMappedEntityException
     */
    public function delete(AdminAccount $adminAccount): void
    {
        $this->_delete($adminAccount);
    }
}
