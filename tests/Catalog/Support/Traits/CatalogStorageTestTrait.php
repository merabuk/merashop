<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support\Traits;

use App\Tests\Shared\Support\Traits\StorageTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @mixin WebTestCase
 */
trait CatalogStorageTestTrait
{
    use StorageTestTrait;

    protected function getStorageServiceId(): string
    {
        return 'catalog.storage.filesystem';
    }
}
