<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support\Traits;

use App\Tests\Shared\Support\Traits\BaseEntityManagerTrait;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait IdentityAccessEntityManagerTrait
{
    use BaseEntityManagerTrait;

    protected function getIdentityAccessEntityManager(): EntityManager
    {
        return self::getContainer()->get('doctrine')->getManager('identity_access');
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->getIdentityAccessEntityManager();
    }
}
