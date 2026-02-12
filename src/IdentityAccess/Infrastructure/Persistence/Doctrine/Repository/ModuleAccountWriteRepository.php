<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\Repository\ModuleAccountWriteRepositoryInterface;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;

final class ModuleAccountWriteRepository extends BaseModuleAccountRepository implements ModuleAccountWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws ValueObjectExceptionInterface
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     */
    public function save(ModuleAccount $moduleAccount): ModuleAccount
    {
        $orm = $this->_save(domain: $moduleAccount, id: $moduleAccount->getId()?->value());

        return $this->mapper->fromDoctrineOrm($orm);
    }

    public function delete(ModuleAccount $moduleAccount): void
    {
        $this->_delete($moduleAccount);
    }
}
