<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeUlidException;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Shared\Domain\ValueObject\Ulid as SharedUlid;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Shared\Unit\Domain\ValueObject\UlidTest as SharedUlidTest;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

final class UlidTest extends TestCase
{
    public function testItCreatesValidUlid(): void
    {
        $ulid = AttributeMother::DEFAULT_ULID;
        $vo = Ulid::fromString($ulid);

        self::assertSame($ulid, $vo->value());
        self::assertSame($ulid, (string) $vo);
    }

    #[DataProviderExternal(SharedUlidTest::class, 'invalidUlidProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidAttributeUlidException::class);
        Ulid::fromString($invalidValue);
    }

    public function testItIsStrictlyTyped(): void
    {
        $ulid = AttributeMother::DEFAULT_ULID;
        $adminUlid = Ulid::fromString($ulid);
        $sharedUlid = SharedUlid::fromString($ulid);

        self::assertFalse($adminUlid->equals($sharedUlid));
    }
}
