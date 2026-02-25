<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject;

use App\IdentityAccess\Domain\Exception\ValueObject\InvalidRoleException;
use App\IdentityAccess\Domain\ValueObject\Role;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function testItCreatesValidRole(): void
    {
        $role = 'ROLE_ADMIN';
        $vo = Role::fromString($role);

        self::assertSame($role, $vo->value());
        self::assertSame($role, (string) $vo);
    }

    public function testItTrimsInput(): void
    {
        $role = 'ROLE_ADMIN';
        $vo = Role::fromString('  '.$role.'  ');

        self::assertSame($role, $vo->value());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $role = 'ROLE_ADMIN';
        $vo1 = Role::fromString($role);
        $vo2 = Role::fromString($role);
        $vo3 = Role::fromString('ROLE_USER');

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
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
