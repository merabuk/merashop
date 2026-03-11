<?php

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\ModuleAccount;

use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountClientIdException;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ClientIdTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validClientIdProvider')]
    public function testItCreatesValidClientId(string $input, string $expected): void
    {
        $vo = ClientId::fromString($input);

        self::assertEquals($expected, $vo->value());
        self::assertEquals($expected, (string) $vo);
    }

    public static function validClientIdProvider(): iterable
    {
        yield 'normal' => ['valid-id', 'valid-id'];
        yield 'with spaces' => ['  trimmed-id  ', 'trimmed-id'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: ClientId::class,
            value: 'valid-client-id',
            anotherValue: 'another-valid-client-id'
        );
    }

    #[DataProvider('invalidClientIdProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidModuleAccountClientIdException::class);

        ClientId::fromString($invalidValue);
    }

    public static function invalidClientIdProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['    '];
        yield 'too short' => [str_repeat('a', ClientId::MIN_LENGTH - 1)];
        yield 'too long' => [str_repeat('a', ClientId::MAX_LENGTH + 1)];
    }
}
