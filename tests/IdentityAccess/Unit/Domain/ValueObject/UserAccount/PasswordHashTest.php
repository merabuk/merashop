<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\UserAccount;

use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountPasswordHashException;
use App\IdentityAccess\Domain\ValueObject\UserAccount\PasswordHash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PasswordHashTest extends TestCase
{
    public function testItCreatesValidPasswordHash(): void
    {
        $passwordHash = 'valid_password_hash';
        $vo = PasswordHash::fromString($passwordHash);

        self::assertSame($passwordHash, $vo->value());
        self::assertSame($passwordHash, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $passwordHash = 'valid_password_hash';
        $vo1 = PasswordHash::fromString($passwordHash);
        $vo2 = PasswordHash::fromString($passwordHash);
        $vo3 = PasswordHash::fromString('another_valid_password_hash');

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    #[DataProvider('invalidPasswordHashProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidUserAccountPasswordHashException::class);
        PasswordHash::fromString($invalidValue);
    }

    public static function invalidPasswordHashProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'only spaces' => ['   '];
        yield 'max length exceeded' => [str_repeat('a', PasswordHash::MAX_LENGTH + 1)];
    }
}
