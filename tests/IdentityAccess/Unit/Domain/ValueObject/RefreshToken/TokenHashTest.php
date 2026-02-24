<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\RefreshToken;

use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenTokenHashException;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TokenHashTest extends TestCase
{
    public function testItCreatesValidTokenHash(): void
    {
        $hash = 'valid_token_hash';
        $vo = TokenHash::fromString($hash);

        self::assertSame($hash, $vo->value());
        self::assertSame($hash, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $hash = 'valid_token_hash';
        $vo1 = TokenHash::fromString($hash);
        $vo2 = TokenHash::fromString($hash);
        $vo3 = TokenHash::fromString('another_valid_token_hash');

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
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
