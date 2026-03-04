<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support\Traits;

use App\Tests\Shared\Support\Traits\BaseEntityManagerTrait;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait CatalogEntityManagerTrait
{
    use BaseEntityManagerTrait;

    protected function getCatalogEntityManager(): EntityManager
    {
        return self::getContainer()->get('doctrine')->getManager('catalog');
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->getCatalogEntityManager();
    }
}
