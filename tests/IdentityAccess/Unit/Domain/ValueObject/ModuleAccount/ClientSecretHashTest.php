<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\ModuleAccount;

use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountPasswordHashException;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientSecretHash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClientSecretHashTest extends TestCase
{
    #[DataProvider('validClientSecretProvider')]
    public function testItCreatesValidClientSecretHash(string $input, string $expected): void
    {
        $hash = 'valid_client_secret_hash';
        $vo = ClientSecretHash::fromString($hash);

        self::assertSame($hash, $vo->value());
        self::assertSame($hash, (string) $vo);
    }

    public static function validClientSecretProvider(): iterable
    {
        yield 'normal' => ['valid-secret', 'valid-secret'];
        yield 'with spaces' => ['  trimmed-secret  ', 'trimmed-secret'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $hash = 'valid_client_secret_hash';
        $vo1 = ClientSecretHash::fromString($hash);
        $vo2 = ClientSecretHash::fromString($hash);
        $vo3 = ClientSecretHash::fromString('another_valid_client_secret_hash');

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    #[DataProvider('invalidClientSecretProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidModuleAccountPasswordHashException::class);

        ClientSecretHash::fromString($invalidValue);
    }

    public static function invalidClientSecretProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['    '];
        yield 'too long' => [str_repeat('a', ClientSecretHash::MAX_LENGTH + 1)];
    }
}
