<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject;

use App\IdentityAccess\Domain\Exception\ValueObject\InvalidScopeException;
use App\IdentityAccess\Domain\ValueObject\Scope;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class ScopeTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

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
        yield 'trimmed' => ['  scope_admin  ', 'scope_admin'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: Scope::class,
            value: 'scope_admin',
            anotherValue: 'scope_user'
        );
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
