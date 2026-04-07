<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Infrastructure\Persistence\Doctrine\Normalizer;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\AttributeOptionMetadataInterface;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\DimensionMetadata;
use App\Catalog\Infrastructure\Persistence\Doctrine\Normalizer\AttributeOptionMetadataNormalizer;
use App\Shared\Domain\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AttributeOptionMetadataNormalizerTest extends TestCase
{
    #[DataProvider('denormalizationDataProvider')]
    public function testItDenormalizes(
        TypeEnum $typeEnum,
        ?array $data,
        ?AttributeOptionMetadataInterface $expected,
    ): void {
        $type = Type::fromEnum($typeEnum);
        $result = $this->createNormalizer()->denormalize($type, $data);

        if (null === $expected) {
            self::assertNull($result);
        } else {
            self::assertTrue($expected->equals($result));
        }
    }

    public static function denormalizationDataProvider(): iterable
    {
        yield 'dimensions with data' => [TypeEnum::Dimension, ['base_ratio' => 0.01], DimensionMetadata::fromFloat(0.01)];
        yield 'dimensions without data' => [TypeEnum::Dimension, [], DimensionMetadata::fromFloat(DimensionMetadata::BASE_RATIO)];
        yield 'others' => [TypeEnum::String, null, null];
    }

    #[DataProvider('normalizationDataProvider')]
    public function testItNormalizes(
        ?AttributeOptionMetadataInterface $metadata,
        ?array $expected,
    ): void {
        $result = $this->createNormalizer()->normalize($metadata);

        self::assertSame($expected, $result);
    }

    public static function normalizationDataProvider(): iterable
    {
        yield 'dimensions' => [DimensionMetadata::fromFloat(0.01), ['base_ratio' => 0.01]];
        yield 'others' => [null, null];
    }

    public function testThrowsExceptionWhenNewMetadataTypeIsNotSupportedByNormalization(): void
    {
        $unsupportedMetadata = $this->createMock(AttributeOptionMetadataInterface::class);

        $this->expectException(InvalidArgumentException::class);

        $this->createNormalizer()->normalize($unsupportedMetadata);
    }

    public function createNormalizer(): AttributeOptionMetadataNormalizer
    {
        return new AttributeOptionMetadataNormalizer();
    }
}
