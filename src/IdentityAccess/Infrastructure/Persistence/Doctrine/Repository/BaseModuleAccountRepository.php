<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmModuleAccount;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper\ModuleAccountMapper;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\BaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseEntityRepository<OrmModuleAccount>
 */
abstract class BaseModuleAccountRepository extends BaseEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected readonly ModuleAccountMapper $mapper,
    ) {
        parent::__construct(registry: $registry, entityClass: static::getEntityClass());
    }

    /**
     * @return class-string<OrmModuleAccount>
     */
    protected function getEntityClass(): string
    {
        return OrmModuleAccount::class;
    }
}
