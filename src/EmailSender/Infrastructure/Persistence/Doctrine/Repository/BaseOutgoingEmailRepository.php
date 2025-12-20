<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Repository;

use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutboxEmail;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Mapper\OutboxEmailMapper;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\BaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseEntityRepository<OrmOutboxEmail>
 */
abstract class BaseOutgoingEmailRepository extends BaseEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected readonly OutboxEmailMapper $mapper,
    ) {
        parent::__construct(registry: $registry, entityClass: static::getEntityClass());
    }

    /**
     * @return class-string<OrmOutboxEmail>
     */
    protected function getEntityClass(): string
    {
        return OrmOutboxEmail::class;
    }
}
