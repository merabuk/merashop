<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryVersionException;
use App\Catalog\Domain\ValueObject\Category\Version;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\VersionTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VersionTest extends TestCase
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
        $this->expectException(InvalidCategoryVersionException::class);
        Version::fromInt($invalidValue);
    }
}
