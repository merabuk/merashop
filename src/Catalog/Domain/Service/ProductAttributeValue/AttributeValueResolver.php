<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\ProductAttributeValue;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\ProductAttributeValue\UnsupportedAttributeTypeException;
use App\Catalog\Domain\ValueObject\ProductAttribute\AttributeValueInterface;
use App\Catalog\Domain\ValueObject\ProductAttribute\BooleanValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\IntegerValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\StringValue;
use App\Shared\Domain\Exception\InvalidArgumentException;

final readonly class AttributeValueResolver
{
    /**
     * @throws UnsupportedAttributeTypeException
     */
    public function resolve(TypeEnum $attributeType, mixed $rawValue): AttributeValueInterface
    {
        return match ($attributeType) {
            TypeEnum::String,
            TypeEnum::Text => new StringValue((string) $rawValue),
            TypeEnum::Integer => new IntegerValue((int) $rawValue),
            TypeEnum::Boolean => new BooleanValue(filter_var($rawValue, FILTER_VALIDATE_BOOLEAN)),
            TypeEnum::Select,
            TypeEnum::MultiSelect => throw new InvalidArgumentException('Options should be handled via OptionId, not ValueInterface'),
            // TODO[attribute value]: add type check for another types in future
            default => throw UnsupportedAttributeTypeException::becauseIsItNotSupportedType($attributeType->value),
        };
    }

    public function isOptionBased(TypeEnum $attributeType): bool
    {
        return in_array($attributeType, $this->getOptionBasedTypes(), true);
    }

    /**
     * @return TypeEnum[]
     */
    private function getOptionBasedTypes(): array
    {
        return [TypeEnum::Select, TypeEnum::MultiSelect];
    }
}
