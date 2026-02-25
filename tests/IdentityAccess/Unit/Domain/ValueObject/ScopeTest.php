<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject;

use App\IdentityAccess\Domain\Exception\ValueObject\InvalidScopeException;
use App\IdentityAccess\Domain\ValueObject\Scope;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    #[DataProvider('validScopeProvider')]
    public function testItCreatesValidScope(string $input, string $expected): void
    {
        $vo = Scope::fromString($input);
        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validScopeProvider(): iterable
    {
        yield 'simple' => ['read', 'read'];
        yield 'dotted hierarchy' => ['user.profile.read', 'user.profile.read'];
        yield 'colon action' => ['orders:write', 'orders:write'];
        yield 'with numbers' => ['v1.products.read', 'v1.products.read'];
        yield 'case normalization' => ['USER.READ', 'user.read'];
        yield 'complex' => ['identity:auth-tokens:manage', 'identity:auth-tokens:manage'];
    }

    public function testItTrimsInput(): void
    {
        $scope = 'scope_admin';
        $vo = Scope::fromString('  '.$scope.'  ');

        self::assertSame($scope, $vo->value());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $scope = 'scope_admin';
        $vo1 = Scope::fromString($scope);
        $vo2 = Scope::fromString($scope);
        $vo3 = Scope::fromString('scope_user');

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    #[DataProvider('invalidScopeProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidInput): void
    {
        $this->expectException(InvalidScopeException::class);
        Scope::fromString($invalidInput);
    }

    public static function invalidScopeProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only special chars' => ['.:-'];
        yield 'starts with dot' => ['.read'];
        yield 'ends with colon' => ['read:'];
        yield 'spaces inside' => ['read users'];
        yield 'illegal chars' => ['read_users$'];
    }
}
