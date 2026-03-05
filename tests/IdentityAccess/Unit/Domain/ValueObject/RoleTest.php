<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject;

use App\IdentityAccess\Domain\Exception\ValueObject\InvalidRoleException;
use App\IdentityAccess\Domain\ValueObject\Role;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validRoleProvider')]
    public function testItCreatesValidRole(string $role, string $expected): void
    {
        $vo = Role::fromString($role);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validRoleProvider(): iterable
    {
        yield 'valid role' => ['ROLE_ADMIN', 'ROLE_ADMIN'];
        yield 'trimmed role' => ['  ROLE_ADMIN  ', 'ROLE_ADMIN'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: Role::class,
            value: 'ROLE_ADMIN',
            anotherValue: 'ROLE_USER',
        );
    }

    #[DataProvider('invalidRoleProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidRoleException::class);

        Role::fromString($invalidValue);
    }

    public static function invalidRoleProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['    '];
        yield 'wrong case' => ['admin'];
        yield 'random string' => ['not-a-role'];
        yield 'without prefix' => ['ADMIN'];
    }
}
