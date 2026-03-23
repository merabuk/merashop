<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeValueIdException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\ProductAttribute\ArrayValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\BooleanValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Id as ProductAttributeValueId;
use App\Catalog\Domain\ValueObject\ProductAttribute\IntegerValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\StringValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductAttributeValue;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;

final readonly class ProductAttributeValueMapper
{
    /**
     * @throws EntityIdMissingException
     * @throws InvalidProductAttributeValueIdException
     * @throws InvalidAttributeIdException
     */
    public function toDomain(OrmProductAttributeValue $orm): ProductAttributeValue
    {
        $id = $orm->id ?? throw EntityIdMissingException::forEntity($orm::class);

        $rawValue = $orm->valueJson['value'] ?? null;
        $type = $orm->attribute->type;

        if (null === $type) {
            throw $this->makeError(sprintf('%s Attribute type is null', $this->getLogPrefix($id)));
        }

        if (null === $rawValue) {
            throw $this->makeError(sprintf('%s Value is missing for attribute ID %d', $this->getLogPrefix($id), $orm->attribute->id));
        }

        $value = match ($type) {
            TypeEnum::String => is_string($rawValue)
                ? StringValue::fromString($rawValue)
                : throw $this->makeTypeError(id: $id, expected: 'string', actual: $rawValue),

            TypeEnum::Integer => is_int($rawValue)
                ? IntegerValue::fromInt($rawValue)
                : throw $this->makeTypeError(id: $id, expected: 'integer', actual: $rawValue),

            TypeEnum::Boolean => is_bool($rawValue)
                ? BooleanValue::fromBool($rawValue)
                : throw $this->makeTypeError(id: $id, expected: 'boolean', actual: $rawValue),

            // TODO[attribute value]: add items check in future
            TypeEnum::Select => is_array($rawValue)
                ? ArrayValue::fromArray($rawValue)
                : throw $this->makeTypeError(id: $id, expected: 'array', actual: $rawValue),
        };

        return new ProductAttributeValue(
            attributeId: AttributeId::fromInt($orm->attribute->id),
            value: $value,
            id: ProductAttributeValueId::fromInt($id)
        );
    }

    public function mapToExistingOrm(ProductAttributeValue $domain, OrmProductAttributeValue $orm): void
    {
        $vo = $domain->getValue();

        $orm->valueJson['value'] = match (true) {
            $vo instanceof StringValue => $vo->value(),
            $vo instanceof IntegerValue => $vo->value(),
            $vo instanceof BooleanValue => $vo->value(),
            $vo instanceof ArrayValue => $vo->value(),
            default => throw $this->makeError(sprintf('Unknown attribute value type: %s', get_debug_type($vo))),
        };
    }

    private function getLogPrefix(int $id): string
    {
        return sprintf('[%s::%d]', OrmProductAttributeValue::class, $id);
    }

    private function makeTypeError(int $id, string $expected, mixed $actual): InvalidArgumentException
    {
        return $this->makeError(sprintf(
            '%s Expected %s for attribute value, got %s',
            $this->getLogPrefix($id),
            $expected,
            get_debug_type($actual)
        ));
    }

    private function makeError(string $message): InvalidArgumentException
    {
        throw new InvalidArgumentException($message);
    }
}
