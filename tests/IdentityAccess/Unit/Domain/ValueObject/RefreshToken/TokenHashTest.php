<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\RefreshToken;

use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenTokenHashException;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TokenHashTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    public function testItCreatesValidTokenHash(): void
    {
        $hash = 'valid_token_hash';
        $vo = TokenHash::fromString($hash);

        self::assertSame($hash, $vo->value());
        self::assertSame($hash, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: TokenHash::class,
            value: 'valid_token_hash',
            anotherValue: 'another_valid_token_hash'
        );
    }

    #[DataProvider('invalidTokenHashProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidRefreshTokenTokenHashException::class);

        TokenHash::fromString($invalidValue);
    }

    public static function invalidTokenHashProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'too long' => [str_repeat('a', TokenHash::MAX_LENGTH + 1)];
    }
}
