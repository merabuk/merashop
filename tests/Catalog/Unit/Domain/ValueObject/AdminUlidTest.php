<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject;

use App\Catalog\Domain\Exception\InvalidAdminUlidException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Shared\Domain\ValueObject\Identity\Ulid as SharedUlid;
use App\Tests\Catalog\Support\ProductMother;
use App\Tests\Shared\Unit\Domain\ValueObject\Identity\UlidTest as SharedUlidTest;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

final class AdminUlidTest extends TestCase
{
    public function testItCreatesValidUlid(): void
    {
        $ulid = ProductMother::DEFAULT_ADMIN_ULID;
        $vo = AdminUlid::fromString($ulid);

        self::assertSame($ulid, $vo->value());
        self::assertSame($ulid, (string) $vo);
    }

    #[DataProviderExternal(SharedUlidTest::class, 'invalidUlidProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidAdminUlidException::class);
        AdminUlid::fromString($invalidValue);
    }

    public function testItIsStrictlyTyped(): void
    {
        $ulid = ProductMother::DEFAULT_ULID;
        $adminUlid = AdminUlid::fromString($ulid);
        $sharedUlid = SharedUlid::fromString($ulid);

        self::assertFalse($adminUlid->equals($sharedUlid));
    }
}
