<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject;

use App\IdentityAccess\Domain\ValueObject\Role;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use Traversable;

final class RoleCollectionTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

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
        $this->assertStringCollectionVOProvidesEqualityCheck(
            className: RoleCollection::class,
            values: ['ROLE_ADMIN', 'ROLE_USER'],
            shuffledValues: ['ROLE_USER', 'ROLE_ADMIN'],
            anotherValues: ['ROLE_ADMIN'],
        );
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
