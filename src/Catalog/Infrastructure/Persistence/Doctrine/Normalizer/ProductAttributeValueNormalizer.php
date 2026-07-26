<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Normalizer;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeColorValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeDateValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeMagnitudeDimensionValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeUrlValueException;
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
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Shared\Domain\Helpers\TypeCastingTrait;

final readonly class ProductAttributeValueNormalizer
{
    use TypeCastingTrait;

    /**
     * @param ?array<string, mixed> $data
     *
     * @throws InvalidProductAttributeColorValueException
     * @throws InvalidProductAttributeDateValueException
     * @throws InvalidProductAttributeMagnitudeDimensionValueException
     * @throws InvalidProductAttributeUrlValueException
     */
    public function denormalize(TypeEnum $type, ?array $data, ?AttributeOptionId $optionId = null): ?AttributeValueInterface
    {
        if (null === $data) {
            return null;
        }

        return match ($type) {
            TypeEnum::Select,
            TypeEnum::MultiSelect => null,
            TypeEnum::String => isset($data['translations']) && is_array($data['translations'])
                ? new LocalizedStringValue(self::castToStringMap(value: $data['translations']))
                : throw $this->makeTypeError('array', $data['translations'] ?? null),
            TypeEnum::Text => isset($data['translations']) && is_array($data['translations'])
                ? new LocalizedTextValue(self::castToStringMap(value: $data['translations']))
                : throw $this->makeTypeError('array', $data['translations'] ?? null),
            TypeEnum::Integer => isset($data['value']) && is_int($data['value'])
                ? IntegerValue::fromInt($data['value'])
                : throw $this->makeTypeError('int', $data['value'] ?? null),
            TypeEnum::Float => isset($data['value']) && is_float($data['value'])
                ? FloatValue::fromFloat($data['value'])
                : throw $this->makeTypeError('float', $data['value'] ?? null),
            TypeEnum::Boolean => isset($data['value']) && is_bool($data['value'])
                ? BooleanValue::fromBool($data['value'])
                : throw $this->makeTypeError('bool', $data['value'] ?? null),
            TypeEnum::Color => isset($data['value']) && is_string($data['value'])
                ? ColorValue::fromString($data['value'])
                : throw $this->makeTypeError('string', $data['value'] ?? null),
            TypeEnum::Date => isset($data['value']) && is_string($data['value'])
                ? DateValue::fromString($data['value'])
                : throw $this->makeTypeError('string', $data['value'] ?? null),
            TypeEnum::Url => isset($data['value']) && is_string($data['value'])
                ? UrlValue::fromString($data['value'])
                : throw $this->makeTypeError('string', $data['value'] ?? null),
            TypeEnum::Dimension => new DimensionValue(
                magnitude: isset($data['magnitude']) && is_float($data['magnitude'])
                    ? $data['magnitude']
                    : throw $this->makeTypeError('float', $data['magnitude'] ?? null),
                unit: $optionId ?? throw new InvalidArgumentException('Dimension unit option id is required'),
            ),
            default => throw new InvalidArgumentException(sprintf('Denormalization logic for type "%s" is missing in normalizer', $type->value)),
        };
    }

    /**
     * @return ?array<string, mixed>
     */
    public function normalize(?AttributeValueInterface $vo): ?array
    {
        if (null === $vo) {
            return null;
        }

        return match (true) {
            $vo instanceof LocalizedStringValue,
            $vo instanceof LocalizedTextValue => ['translations' => $vo->value()],

            $vo instanceof IntegerValue,
            $vo instanceof FloatValue,
            $vo instanceof BooleanValue,
            $vo instanceof ColorValue,
            $vo instanceof UrlValue => ['value' => $vo->value()],

            $vo instanceof DateValue => ['value' => (string) $vo],

            $vo instanceof DimensionValue => [
                'magnitude' => $vo->magnitude(),
            ],

            default => throw new InvalidArgumentException(sprintf('Normalization logic for class "%s" is missing in normalizer', get_debug_type($vo))),
        };
    }

    private function makeTypeError(string $expected, mixed $actual): InvalidArgumentException
    {
        return new InvalidArgumentException(sprintf(
            'Invalid data type in JSONB. Expected %s, got %s',
            $expected,
            get_debug_type($actual)
        ));
    }
}
