<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountPasswordHashException;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordHash;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class PasswordHashTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    public function testItCreatesValidPasswordHash(): void
    {
        $passwordHash = 'valid_password_hash';
        $vo = PasswordHash::fromString($passwordHash);

        self::assertSame($passwordHash, $vo->value());
        self::assertSame($passwordHash, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: PasswordHash::class,
            value: 'valid_password_hash',
            anotherValue: 'another_valid_password_hash'
        );
    }

    #[DataProvider('invalidPasswordHashProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidAdminAccountPasswordHashException::class);
        PasswordHash::fromString($invalidValue);
    }

    public static function invalidPasswordHashProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'only spaces' => ['   '];
        yield 'max length exceeded' => [str_repeat('a', PasswordHash::MAX_LENGTH + 1)];
    }
}
