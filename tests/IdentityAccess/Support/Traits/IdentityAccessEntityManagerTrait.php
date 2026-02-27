<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support\Traits;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait IdentityAccessEntityManagerTrait
{
    protected function getIdentityAccessEntityManager(): EntityManager
    {
        return self::getContainer()->get('doctrine')->getManager('identity_access');
    }

    protected function findOrmEntity(string $entityClass, int|string $id): object
    {
        return $this->getIdentityAccessEntityManager()->find($entityClass, $id);
    }

    protected function clearEntityManager(): void
    {
        $this->getIdentityAccessEntityManager()->clear();
    }
}
