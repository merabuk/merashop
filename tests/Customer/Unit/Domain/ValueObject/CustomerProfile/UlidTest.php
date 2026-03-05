<?php

declare(strict_types=1);

namespace App\Tests\Customer\Unit\Domain\ValueObject\CustomerProfile;

use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfileUlidException;
use App\Customer\Domain\ValueObject\CustomerProfile\Ulid;
use App\Shared\Domain\ValueObject\Ulid as SharedUlid;
use App\Tests\Customer\Support\CustomerProfileMother;
use App\Tests\Shared\Unit\Domain\ValueObject\UlidTest as SharedUlidTest;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

final class UlidTest extends TestCase
{
    public function testItCreatesValidUlid(): void
    {
        $ulid = CustomerProfileMother::DEFAULT_USER_ULID;
        $vo = Ulid::fromString($ulid);

        self::assertSame($ulid, $vo->value());
        self::assertSame($ulid, (string) $vo);
    }

    #[DataProviderExternal(SharedUlidTest::class, 'invalidUlidProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidCustomerProfileUlidException::class);
        Ulid::fromString($invalidValue);
    }

    public function testItIsStrictlyTyped(): void
    {
        $ulid = CustomerProfileMother::DEFAULT_USER_ULID;
        $adminUlid = Ulid::fromString($ulid);
        $sharedUlid = SharedUlid::fromString($ulid);

        self::assertFalse($adminUlid->equals($sharedUlid));
    }
}
