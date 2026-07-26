<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\UserAccount;

use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountUlidException;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Ulid;
use App\Shared\Domain\ValueObject\Identity\Ulid as SharedUlid;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Identity\UlidTest as SharedUlidTest;
use PHPUnit\Framework\Attributes\DataProviderExternal;

final class UlidTest extends BaseUnitTest
{
    public function testItCreatesValidUlid(): void
    {
        $ulid = UserAccountMother::DEFAULT_ULID;
        $vo = Ulid::fromString($ulid);

        self::assertSame($ulid, $vo->value());
        self::assertSame($ulid, (string) $vo);
    }

    #[DataProviderExternal(SharedUlidTest::class, 'invalidUlidProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidUserAccountUlidException::class);
        Ulid::fromString($invalidValue);
    }

    public function testItIsStrictlyTyped(): void
    {
        $ulid = UserAccountMother::DEFAULT_ULID;
        $adminUlid = Ulid::fromString($ulid);
        $sharedUlid = SharedUlid::fromString($ulid);

        self::assertFalse($adminUlid->equals($sharedUlid));
    }
}
