<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait BaseEntityManagerTrait
{
    abstract protected function getEntityManager(): EntityManager;

    protected function findOrmEntity(string $entityClass, int|string $id): object
    {
        return $this->getEntityManager()->find($entityClass, $id);
    }

    protected function clearEntityManager(): void
    {
        $this->getEntityManager()->clear();
    }
}
