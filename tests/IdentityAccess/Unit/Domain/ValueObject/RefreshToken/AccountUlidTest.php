<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\RefreshToken;

use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenAccountUlidException;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\AccountUlid;
use App\Shared\Domain\ValueObject\Ulid as SharedUlid;
use App\Tests\Shared\Unit\Domain\ValueObject\UlidTest as SharedUlidTest;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

final class AccountUlidTest extends TestCase
{
    public function testItCreatesValidUlid(): void
    {
        $ulid = '01H7B6P9Y8M1V5X2A7S4D3F6G8';
        $vo = AccountUlid::fromString($ulid);

        self::assertSame($ulid, $vo->value());
        self::assertSame($ulid, (string) $vo);
    }

    #[DataProviderExternal(SharedUlidTest::class, 'invalidUlidProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidRefreshTokenAccountUlidException::class);
        AccountUlid::fromString($invalidValue);
    }

    public function testItIsStrictlyTyped(): void
    {
        $ulid = '01H7B6P9Y8M1V5X2A7S4D3F6G8';
        $adminUlid = AccountUlid::fromString($ulid);
        $sharedUlid = SharedUlid::fromString($ulid);

        self::assertFalse($adminUlid->equals($sharedUlid));
    }
}
