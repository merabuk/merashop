<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support\Traits;

use App\Tests\IdentityAccess\Support\ModuleAccountFixture;
use App\Tests\IdentityAccess\Support\ModuleAccountMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait ModuleAccountFactoryTrait
{
    protected function getModuleAccountMother(): ModuleAccountMother
    {
        return self::getContainer()->get(ModuleAccountMother::class);
    }

    protected function getModuleAccountFixture(): ModuleAccountFixture
    {
        return self::getContainer()->get(ModuleAccountFixture::class);
    }
}
