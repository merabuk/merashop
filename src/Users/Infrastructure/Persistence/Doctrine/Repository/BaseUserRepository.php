<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Repository;

use App\Users\Infrastructure\Persistence\Doctrine\Entity\OrmUser;
use App\Users\Infrastructure\Persistence\Doctrine\Mapper\UserMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class BaseUserRepository extends ServiceEntityRepository
{
    public const ORM_ENTITY_CLASS_NAME = OrmUser::class;

    public function __construct(
        ManagerRegistry $registry,
        protected readonly UserMapper $mapper,
    ) {
        parent::__construct(registry: $registry, entityClass: self::ORM_ENTITY_CLASS_NAME);
    }
}
