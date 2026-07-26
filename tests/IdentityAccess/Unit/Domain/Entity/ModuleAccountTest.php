<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\Entity;

use App\IdentityAccess\Domain\ValueObject\ScopeCollection;
use App\Tests\IdentityAccess\Support\ModuleAccountMother;
use App\Tests\Shared\BaseUnitTest;

final class ModuleAccountTest extends BaseUnitTest
{
    public function testItUpdatesScopesCorrectly(): void
    {
        $module = ModuleAccountMother::createWithData(scopes: ['SCOPE_OLD']);

        $newScopes = ScopeCollection::fromStrings(['SCOPE_NEW']);

        $module->updateScopes($newScopes);

        self::assertTrue($module->getScopes()->equals($newScopes));
    }
}
