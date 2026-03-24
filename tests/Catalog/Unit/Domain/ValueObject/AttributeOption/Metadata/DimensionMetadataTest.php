<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\AttributeOption\Metadata;

use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionDimensionMetadataException;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\DimensionMetadata;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DimensionMetadataTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validDimensionMetadataProvider')]
    public function testItCreatesValidDimensionMetadata(float $value): void
    {
        $vo = DimensionMetadata::fromFloat($value);

        self::assertSame($value, $vo->getBaseRatio());
        self::assertSame((string) $value, (string) $vo);
    }

    public static function validDimensionMetadataProvider(): array
    {
        return [
            'positive value' => [10.5],
            'zero value' => [0.0],
        ];
    }

    public function testItCreatesDimensionMetadataFromNullableFloat(): void
    {
        $vo = DimensionMetadata::fromNullableFloat(null);

        self::assertSame(DimensionMetadata::BASE_RATIO, $vo->getBaseRatio());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertFloatVOProvidesEqualityCheck(
            className: DimensionMetadata::class,
            value: 10.5,
            anotherValue: 10.6
        );
    }

    public function testThrowsExceptionOnInvalidInput(): void
    {
        $this->expectException(InvalidAttributeOptionDimensionMetadataException::class);

        DimensionMetadata::fromFloat(-10.5);
    }
}
