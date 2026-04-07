<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Infrastructure\Persistence\Doctrine\Normalizer;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Enum\Attribute\TypeEnum as AttributeTypeEnum;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\AttributeValueInterface;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\BooleanValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\ColorValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\DateValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\DimensionValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\FloatValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\IntegerValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\LocalizedStringValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\LocalizedTextValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\UrlValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Normalizer\ProductAttributeValueNormalizer;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProductAttributeValueNormalizerTest extends TestCase
{
    use ValueObjectAssertionTrait;

    #[DataProvider('denormalizationDataProvider')]
    public function testItDenormalizes(
        AttributeTypeEnum $type,
        ?array $data,
        ?AttributeOptionId $optionId,
        ?AttributeValueInterface $expected,
    ): void {
        $result = $this->createNormalizer()->denormalize(type: $type, data: $data, optionId: $optionId);

        $this->assertVoEqualsOrNull($expected, $result);
    }

    public static function denormalizationDataProvider(): iterable
    {
        yield 'localized string' => [
            'type' => TypeEnum::String,
            'data' => ['translations' => self::getLocalizedData()],
            'optionId' => null,
            'expected' => LocalizedStringValue::fromArray(self::getLocalizedData()),
        ];
        yield 'localized text' => [
            'type' => TypeEnum::Text,
            'data' => ['translations' => self::getLocalizedData()],
            'optionId' => null,
            'expected' => LocalizedTextValue::fromArray(self::getLocalizedData()),
        ];
        yield 'integer' => [
            'type' => TypeEnum::Integer,
            'data' => ['value' => 123],
            'optionId' => null,
            'expected' => IntegerValue::fromInt(123),
        ];
        yield 'float' => [
            'type' => TypeEnum::Float,
            'data' => ['value' => 123.45],
            'optionId' => null,
            'expected' => FloatValue::fromFloat(123.45),
        ];
        yield 'boolean' => [
            'type' => TypeEnum::Boolean,
            'data' => ['value' => true],
            'optionId' => null,
            'expected' => BooleanValue::fromBool(true),
        ];
        yield 'color' => [
            'type' => TypeEnum::Color,
            'data' => ['value' => '#FFFFFF'],
            'optionId' => null,
            'expected' => ColorValue::fromString('#FFFFFF'),
        ];
        yield 'url' => [
            'type' => TypeEnum::Url,
            'data' => ['value' => 'https://example.com'],
            'optionId' => null,
            'expected' => UrlValue::fromString('https://example.com'),
        ];
        yield 'date' => [
            'type' => TypeEnum::Date,
            'data' => ['value' => '2023-01-01'],
            'optionId' => null,
            'expected' => DateValue::fromString('2023-01-01'),
        ];
        yield 'dimension' => [
            'type' => TypeEnum::Dimension,
            'data' => ['magnitude' => 123.45],
            'optionId' => AttributeOptionId::fromInt(123),
            'expected' => new DimensionValue(magnitude: 123.45, unit: AttributeOptionId::fromInt(123)),
        ];
        yield 'other' => [
            'type' => TypeEnum::Image,
            'data' => null,
            'optionId' => null,
            'expected' => null,
        ];
    }

    #[DataProvider('invalidDenormalizationDataProvider')]
    public function testThrowsExceptionWhenNewValueTypeIsNotSupportedByDenormalization(
        AttributeTypeEnum $type,
        ?array $data,
        ?AttributeOptionId $optionId,
        string $expectedException,
    ): void {
        $this->expectException($expectedException);

        $this->createNormalizer()->denormalize(type: $type, data: $data, optionId: $optionId);
    }

    public static function invalidDenormalizationDataProvider(): iterable
    {
        yield 'unsupported type' => [
            'type' => TypeEnum::Image,
            'data' => [],
            'optionId' => null,
            'expectedException' => InvalidArgumentException::class,
        ];
        yield 'missed option' => [
            'type' => TypeEnum::Dimension,
            'data' => ['magnitude' => 123.45],
            'optionId' => null,
            'expectedException' => InvalidArgumentException::class,
        ];
    }

    #[DataProvider('normalizationDataProvider')]
    public function testItNormalizes(?AttributeValueInterface $value, ?array $expected): void
    {
        $result = $this->createNormalizer()->normalize($value);

        self::assertSame($expected, $result);
    }

    public static function normalizationDataProvider(): iterable
    {
        yield 'localized string' => [
            'value' => LocalizedStringValue::fromArray(self::getLocalizedData()),
            'expected' => ['translations' => self::getLocalizedData()],
        ];
        yield 'localized text' => [
            'value' => LocalizedTextValue::fromArray(self::getLocalizedData()),
            'expected' => ['translations' => self::getLocalizedData()],
        ];
        yield 'integer' => [
            'value' => IntegerValue::fromInt(123),
            'expected' => ['value' => 123],
        ];
        yield 'float' => [
            'value' => FloatValue::fromFloat(123.45),
            'expected' => ['value' => 123.45],
        ];
        yield 'boolean' => [
            'value' => BooleanValue::fromBool(true),
            'expected' => ['value' => true],
        ];
        yield 'color' => [
            'value' => ColorValue::fromString('#FFFFFF'),
            'expected' => ['value' => '#FFFFFF'],
        ];
        yield 'url' => [
            'value' => UrlValue::fromString('https://example.com'),
            'expected' => ['value' => 'https://example.com'],
        ];
        yield 'date' => [
            'value' => DateValue::fromString('2023-01-01'),
            'expected' => ['value' => '2023-01-01'],
        ];
        yield 'dimension' => [
            'value' => new DimensionValue(magnitude: 123.45, unit: AttributeOptionId::fromInt(123)),
            'expected' => ['magnitude' => 123.45],
        ];
        yield 'other' => [
            'value' => null,
            'expected' => null,
        ];
    }

    public function testThrowsExceptionWhenNewValueTypeIsNotSupportedByNormalization(): void
    {
        $unsupportedValue = $this->createMock(AttributeValueInterface::class);

        $this->expectException(InvalidArgumentException::class);

        $this->createNormalizer()->normalize($unsupportedValue);
    }

    public function createNormalizer(): ProductAttributeValueNormalizer
    {
        return new ProductAttributeValueNormalizer();
    }

    private static function getLocalizedData(): array
    {
        return [
            'en' => 'En value',
            'uk' => 'Укр значення',
        ];
    }
}
