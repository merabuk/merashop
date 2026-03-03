<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject;

use App\IdentityAccess\Domain\ValueObject\Scope;
use App\IdentityAccess\Domain\ValueObject\ScopeCollection;
use PHPUnit\Framework\TestCase;
use Traversable;

class ScopeCollectionTest extends TestCase
{
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
        $scopes1 = ['scope_admin', 'scope_user'];
        $scopes2 = ['scope_user', 'scope_admin'];
        $scopes3 = ['scope_admin'];
        $vo1 = ScopeCollection::fromStrings($scopes1);
        $vo2 = ScopeCollection::fromStrings($scopes2);
        $vo3 = ScopeCollection::fromStrings($scopes3);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
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
