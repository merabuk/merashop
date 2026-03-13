<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\ProductAttribute\InvalidProductAttributeIdException;
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
     * @throws InvalidProductAttributeIdException
     * @throws InvalidAttributeIdException
     */
    public function toDomain(OrmProductAttributeValue $orm): ProductAttributeValue
    {
        $id = $orm->id ?? throw EntityIdMissingException::forEntity($orm::class);

        $value = match ($orm->attribute->type) {
            TypeEnum::String => StringValue::fromString((string) $orm->valueString),
            TypeEnum::Int => IntegerValue::fromInt((int) $orm->valueInt),
            TypeEnum::Boolean => BooleanValue::fromBool((bool) $orm->valueBoolean),
            TypeEnum::Select => ArrayValue::fromArray((array) $orm->valueJson),
            null => throw $this->makeError(sprintf('%s with id %d has null type', $orm::class, (int) $orm->id)),
        };

        return new ProductAttributeValue(
            id: ProductAttributeValueId::fromInt($id),
            attributeId: AttributeId::fromInt($orm->attribute->id),
            value: $value
        );
    }

    public function mapToExistingOrm(ProductAttributeValue $domain, OrmProductAttributeValue $orm): void
    {
        $vo = $domain->getValue();

        $orm->valueString = null;
        $orm->valueInt = null;
        $orm->valueBoolean = null;
        $orm->valueJson = null;

        match (true) {
            $vo instanceof StringValue => $orm->valueString = $vo->value(),
            $vo instanceof IntegerValue => $orm->valueInt = $vo->value(),
            $vo instanceof BooleanValue => $orm->valueBoolean = $vo->value(),
            $vo instanceof ArrayValue => $orm->valueJson = $vo->value(),
            default => throw $this->makeError(sprintf('Unknown attribute value type: %s', get_debug_type($vo))),
        };
    }

    private function makeError(string $message): InvalidArgumentException
    {
        throw new InvalidArgumentException($message);
    }
}
