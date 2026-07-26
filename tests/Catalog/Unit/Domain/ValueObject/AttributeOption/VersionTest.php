<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\AttributeOption;

use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionVersionException;
use App\Catalog\Domain\ValueObject\AttributeOption\Version;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\VersionTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class VersionTest extends BaseUnitTest
{
    use VersionTestTrait;

    public function testItCreatesValidVersion(): void
    {
        $this->assertValidVersion(Version::class);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertVersionEquality(Version::class);
    }

    public function testItInitializeCorrectly(): void
    {
        $vo = Version::initial();

        self::assertSame(1, $vo->value());
    }

    #[DataProvider('invalidVersionProvider')]
    public function testThrowsExceptionOnInvalidInput(int $invalidValue): void
    {
        $this->expectException(InvalidAttributeOptionVersionException::class);
        Version::fromInt($invalidValue);
    }
}
