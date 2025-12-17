<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Repository;

use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutgoingEmail;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Mapper\OutgoingEmailMapper;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\BaseEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseEntityRepository<OrmOutgoingEmail>
 */
abstract class BaseOutgoingEmailRepository extends BaseEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected readonly OutgoingEmailMapper $mapper,
    ) {
        parent::__construct(registry: $registry, entityClass: static::getEntityClass());
    }

    /**
     * @return class-string<OrmOutgoingEmail>
     */
    protected function getEntityClass(): string
    {
        return OrmOutgoingEmail::class;
    }
}
