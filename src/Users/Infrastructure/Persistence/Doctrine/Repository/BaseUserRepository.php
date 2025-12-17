<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Infrastructure\Persistence\Doctrine\Repository\BaseEntityRepository;
use App\Users\Infrastructure\Persistence\Doctrine\Entity\OrmUser;
use App\Users\Infrastructure\Persistence\Doctrine\Mapper\UserMapper;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseEntityRepository<OrmUser>
 */
abstract class BaseUserRepository extends BaseEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected readonly UserMapper $mapper,
    ) {
        parent::__construct(registry: $registry, entityClass: static::getEntityClass());
    }

    /**
     * @return class-string<OrmUser>
     */
    protected function getEntityClass(): string
    {
        return OrmUser::class;
    }
}
