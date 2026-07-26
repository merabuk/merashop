<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\ModuleAccount;

use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountPasswordHashException;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientSecretHash;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class ClientSecretHashTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

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
        $this->assertStringVOProvidesEqualityCheck(
            className: ClientSecretHash::class,
            value: 'valid_client_secret_hash',
            anotherValue: 'another_valid_client_secret_hash'
        );
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
