<?php

declare(strict_types=1);

namespace App\Tests\Customer\Support\Traits;

use App\Tests\Shared\Support\Traits\BaseEntityManagerTrait;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait CustomerEntityManagerTrait
{
    use BaseEntityManagerTrait;

    protected function getCustomerEntityManager(): EntityManager
    {
        return self::getContainer()->get('doctrine')->getManager('customer');
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->getCustomerEntityManager();
    }
}
