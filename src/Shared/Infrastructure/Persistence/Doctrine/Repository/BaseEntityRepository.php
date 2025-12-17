<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @template T of object
 */
abstract class BaseEntityRepository extends ServiceEntityRepository
{
    /**
     * @return class-string<T>
     */
    abstract protected function getEntityClass(): string;
}
