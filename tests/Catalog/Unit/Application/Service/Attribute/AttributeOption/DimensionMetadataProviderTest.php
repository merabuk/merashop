<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service\Attribute\AttributeOption;

use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Application\Service\Attribute\AttributeOption\DimensionMetadataProvider;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionDimensionMetadataException;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\DimensionMetadata;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DimensionMetadataProviderTest extends TestCase
{
    public function testGetDefaultIndexName(): void
    {
        self::assertSame(TypeEnum::Dimension->value, $this->createProvider()::getDefaultIndexName());
    }

    #[DataProvider('baseRatioProvider')]
    public function testItHandleCorrectly(?float $baseRatio, float $expected): void
    {
        $data = $this->getData($baseRatio);

        $metadata = $this->createProvider()->handle($data);

        self::assertSame($expected, $metadata->getBaseRatio());
    }

    public static function baseRatioProvider(): iterable
    {
        yield 'valid' => [0.001, 0.001];
        yield 'null' => [null, DimensionMetadata::BASE_RATIO];
    }

    public function testThrowsExceptionWhenBaseRatioIsInvalid(): void
    {
        $baseRatio = -0.001;
        $data = $this->getData($baseRatio);

        $this->expectException(InvalidAttributeOptionDimensionMetadataException::class);

        $this->createProvider()->handle($data);
    }

    private function getData(?float $baseRatio): AttributeOptionData
    {
        return new AttributeOptionData(
            code: 'code',
            translations: [],
            isActive: true,
            baseRatio: $baseRatio,
        );
    }

    private function createProvider(): DimensionMetadataProvider
    {
        return new DimensionMetadataProvider();
    }
}
