<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmUserAccount;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper\UserAccountMapper;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\BaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseEntityRepository<OrmUserAccount>
 */
abstract class BaseUserAccountRepository extends BaseEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected readonly UserAccountMapper $mapper,
    ) {
        parent::__construct(registry: $registry, entityClass: static::getEntityClass());
    }

    /**
     * @return class-string<OrmUserAccount>
     */
    protected function getEntityClass(): string
    {
        return OrmUserAccount::class;
    }
}
