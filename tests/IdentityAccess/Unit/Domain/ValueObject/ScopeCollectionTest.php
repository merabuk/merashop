<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject;

use App\IdentityAccess\Domain\ValueObject\Scope;
use App\IdentityAccess\Domain\ValueObject\ScopeCollection;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use Traversable;

final class ScopeCollectionTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    public function testItCreatesValidScopeCollection(): void
    {
        $scopes = ['scope_admin', 'scope_user'];
        $vo = ScopeCollection::fromStrings($scopes);

        self::assertCount(count($scopes), $vo);
        self::assertSame($scopes, $vo->toStrings());
        self::assertSame(implode(' ', $scopes), (string) $vo);
        self::assertInstanceOf(Traversable::class, $vo->getIterator());
        foreach ($vo->all() as $role) {
            self::assertInstanceOf(Scope::class, $role);
        }
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringCollectionVOProvidesEqualityCheck(
            className: ScopeCollection::class,
            values: ['scope_admin', 'scope_user'],
            shuffledValues: ['scope_user', 'scope_admin'],
            anotherValues: ['scope_admin'],
        );
    }

    public function testItEnsuresUniqueness(): void
    {
        $scopes = ['scope_admin', 'scope_admin', 'scope_user'];
        $vo = ScopeCollection::fromStrings($scopes);

        self::assertCount(2, $vo);
        self::assertSame(['scope_admin', 'scope_user'], $vo->toStrings());
    }

    public function testItContainsScope(): void
    {
        $scopes = ['user:read', 'user:write'];
        $vo = ScopeCollection::fromStrings($scopes);

        self::assertTrue($vo->contains('user:read'));
        self::assertFalse($vo->contains('invalid-scope'));
    }
}
