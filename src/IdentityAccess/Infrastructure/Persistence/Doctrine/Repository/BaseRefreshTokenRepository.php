<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmRefreshToken;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper\RefreshTokenMapper;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\BaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseEntityRepository<OrmRefreshToken>
 */
abstract class BaseRefreshTokenRepository extends BaseEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected readonly RefreshTokenMapper $mapper,
    ) {
        parent::__construct($registry, $this->getEntityClass());
    }

    protected function getEntityClass(): string
    {
        return OrmRefreshToken::class;
    }
}
