<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmAdminAccount;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper\AdminAccountMapper;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\BaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseEntityRepository<OrmAdminAccount>
 */
abstract class BaseAdminAccountRepository extends BaseEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected readonly AdminAccountMapper $mapper,
    ) {
        parent::__construct(registry: $registry, entityClass: static::getEntityClass());
    }

    /**
     * @return class-string<OrmAdminAccount>
     */
    protected function getEntityClass(): string
    {
        return OrmAdminAccount::class;
    }
}
