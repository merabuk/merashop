<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Normalizer;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeDateValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeLocalizedStringValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeLocalizedTextValueException;
use App\Catalog\Domain\ValueObject\ProductAttribute\Value\AttributeValueInterface;
use App\Catalog\Domain\ValueObject\ProductAttribute\Value\BooleanValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Value\ColorValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Value\DateValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Value\DimensionValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Value\FloatValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Value\IntegerValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Value\LocalizedStringValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Value\LocalizedTextValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Value\UrlValue;
use App\Shared\Domain\Exception\InvalidArgumentException;

final readonly class ProductAttributeValueNormalizer
{
    /**
     * @param ?array<string, mixed> $data
     *
     * @throws InvalidProductAttributeLocalizedStringValueException
     * @throws InvalidProductAttributeLocalizedTextValueException
     * @throws InvalidProductAttributeDateValueException
     */
    public function denormalize(TypeEnum $type, ?array $data): ?AttributeValueInterface
    {
        if (null === $data) {
            return null;
        }

        return match ($type) {
            TypeEnum::Select,
            TypeEnum::MultiSelect => null,
            TypeEnum::String => LocalizedStringValue::fromArray($data['translations'] ?? []),
            TypeEnum::Text => LocalizedTextValue::fromArray($data['translations'] ?? []),
            TypeEnum::Integer => IntegerValue::fromInt((int) ($data['value'] ?? 0)),
            TypeEnum::Float => FloatValue::fromFloat((float) ($data['value'] ?? 0.0)),
            TypeEnum::Boolean => BooleanValue::fromBool((bool) ($data['value'] ?? false)),
            TypeEnum::Color => ColorValue::fromString((string) ($data['value'] ?? '')),
            TypeEnum::Date => DateValue::fromString((string) ($data['value'] ?? '')),
            TypeEnum::Url => UrlValue::fromString((string) ($data['value'] ?? '')),
            TypeEnum::Dimension => new DimensionValue(
                magnitude: (float) ($data['magnitude'] ?? 0),
                unit: (string) ($data['ulid'] ?? '')
            ),
            default => throw new InvalidArgumentException(sprintf('Normalization for type %s not implemented', $type->value)),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(AttributeValueInterface $vo): array
    {
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
                'ulid' => $vo->unit(),
            ],

            default => throw new InvalidArgumentException(sprintf('Denormalization for %s not implemented', get_debug_type($vo))),
        };
    }
}
