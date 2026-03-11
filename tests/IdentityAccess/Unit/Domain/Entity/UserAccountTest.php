<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\Entity;

use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use PHPUnit\Framework\TestCase;

final class UserAccountTest extends TestCase
{
    public function testItUpdatesScopesCorrectly(): void
    {
        $user = UserAccountMother::createWithData(roles: ['ROLE_OLD']);

        $newRoles = RoleCollection::fromStrings(['ROLE_NEW']);

        $user->updateRoles($newRoles);

        self::assertTrue($user->getRoles()->equals($newRoles));
    }
}
