<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\Repository\ModuleAccountWriteRepositoryInterface;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmModuleAccount;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class ModuleAccountWriteRepository extends BaseModuleAccountRepository implements ModuleAccountWriteRepositoryInterface
{
    /**
     * @use WriteRepositoryTrait<ModuleAccount, OrmModuleAccount>
     */
    use WriteRepositoryTrait;

    /**
     * @throws EntityFieldMissingException
     * @throws ValueObjectExceptionInterface
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     */
    public function save(ModuleAccount $moduleAccount): ModuleAccount
    {
        $orm = $this->_save(domain: $moduleAccount, id: $moduleAccount->getId()?->value());

        return $this->mapper->fromDoctrineOrm($orm);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(ModuleAccount $moduleAccount): void
    {
        $this->_delete($moduleAccount->getId()?->value());
    }
}
