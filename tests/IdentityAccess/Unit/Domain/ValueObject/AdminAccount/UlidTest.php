<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountUlidException;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid as SharedUlid;
use App\Tests\Shared\Unit\Domain\ValueObject\UlidTest as SharedUlidTest;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

final class UlidTest extends TestCase
{
    /**
     * @throws InvalidAdminAccountUlidException
     */
    public function testItCreatesValidUlid(): void
    {
        $ulid = '01KHVRCA0FCCYAQT1P88R317DD';
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

    /**
     * @throws InvalidAdminAccountUlidException
     * @throws InvalidUlidException
     */
    public function testItIsStrictlyTyped(): void
    {
        $value = '01KHVRCA0FCCYAQT1P88R317DD';
        $adminUlid = Ulid::fromString($value);
        $sharedUlid = SharedUlid::fromString($value);

        self::assertFalse($adminUlid->equals($sharedUlid));
    }
}
