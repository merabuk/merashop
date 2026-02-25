<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject;

use App\IdentityAccess\Domain\ValueObject\Role;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use PHPUnit\Framework\TestCase;
use Traversable;

class RoleCollectionTest extends TestCase
{
    public function testItCreatesValidRoleCollection(): void
    {
        $roles = ['ROLE_ADMIN', 'ROLE_USER'];
        $vo = RoleCollection::fromStrings($roles);

        self::assertCount(count($roles), $vo);
        self::assertSame($roles, $vo->toStrings());
        self::assertSame(implode(' ', $roles), (string) $vo);
        self::assertInstanceOf(Traversable::class, $vo->getIterator());
        foreach ($vo->all() as $role) {
            self::assertInstanceOf(Role::class, $role);
        }
    }

    public function testItProvidesEqualityCheck(): void
    {
        $roles1 = ['ROLE_ADMIN', 'ROLE_USER'];
        $roles2 = ['ROLE_USER', 'ROLE_ADMIN'];
        $roles3 = ['ROLE_ADMIN'];
        $vo1 = RoleCollection::fromStrings($roles1);
        $vo2 = RoleCollection::fromStrings($roles2);
        $vo3 = RoleCollection::fromStrings($roles3);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    public function testItEnsuresUniqueness(): void
    {
        $roles = ['ROLE_ADMIN', 'ROLE_ADMIN', 'ROLE_USER'];
        $vo = RoleCollection::fromStrings($roles);

        self::assertCount(2, $vo);
    }

    public function testItContainsRole(): void
    {
        $roles = ['ROLE_ADMIN', 'ROLE_USER'];
        $vo = RoleCollection::fromStrings($roles);

        self::assertTrue($vo->contains('ROLE_ADMIN'));
        self::assertFalse($vo->contains('invalid-role'));
    }
}
