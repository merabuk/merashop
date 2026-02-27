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
        /** @var ModuleAccountMother $mother */
        $mother = self::getContainer()->get(ModuleAccountMother::class);

        return $mother;
    }

    protected function getModuleAccountFixture(): ModuleAccountFixture
    {
        /** @var ModuleAccountFixture $fixture */
        $fixture = self::getContainer()->get(ModuleAccountFixture::class);

        return $fixture;
    }
}
