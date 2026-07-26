<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountUlidException;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid;
use App\Shared\Domain\ValueObject\Identity\Ulid as SharedUlid;
use App\Tests\IdentityAccess\Support\AdminAccountMother;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Identity\UlidTest as SharedUlidTest;
use PHPUnit\Framework\Attributes\DataProviderExternal;

final class UlidTest extends BaseUnitTest
{
    public function testItCreatesValidUlid(): void
    {
        $ulid = AdminAccountMother::DEFAULT_ULID;
        $vo = Ulid::fromString($ulid);

        self::assertSame($ulid, $vo->value());
        self::assertSame($ulid, (string) $vo);
    }

    #[DataProviderExternal(SharedUlidTest::class, 'invalidUlidProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidAdminAccountUlidException::class);
        Ulid::fromString($invalidValue);
    }

    public function testItIsStrictlyTyped(): void
    {
        $ulid = AdminAccountMother::DEFAULT_ULID;
        $adminUlid = Ulid::fromString($ulid);
        $sharedUlid = SharedUlid::fromString($ulid);

        self::assertFalse($adminUlid->equals($sharedUlid));
    }
}
