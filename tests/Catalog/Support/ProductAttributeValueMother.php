<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum as AttributeTypeEnum;
use App\Catalog\Domain\Factory\Contract\ProductAttributeValueFactoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\ProductAttribute\Id as ProductAttributeId;
use Faker\Generator;

final readonly class ProductAttributeValueMother
{
    public function __construct(
        private ProductAttributeValueFactoryInterface $productAttributeValueFactory,
        private Generator $faker,
    ) {
    }

    public static function createWithData(
        int $attributeId,
        mixed $value = null,
        ?int $id = null,
    ): ProductAttributeValue {
        $value ??= 'product attribute string value';
        $valueObject = ProductAttributeValue::resolveValue($value);

        return new ProductAttributeValue(
            attributeId: AttributeId::fromInt($attributeId),
            value: $valueObject,
            id: $id ? ProductAttributeId::fromInt($id) : null
        );
    }

    public function create(
        int $attributeId,
        ?AttributeTypeEnum $attributeType = null,
        mixed $value = null,
    ): ProductAttributeValue {
        $attributeType ??= AttributeTypeEnum::String;
        $value ??= match ($attributeType) {
            AttributeTypeEnum::String => $this->faker->word(),
            AttributeTypeEnum::Int => $this->faker->numberBetween(1, 1000),
            AttributeTypeEnum::Boolean => $this->faker->boolean(),
            AttributeTypeEnum::Select => $this->faker->randomElements(
                array: ['Option 1', 'Option 2', 'Option 3', 'Option 4', 'Option 5'],
                count: random_int(2, 5)
            ),
        };

        return $this->productAttributeValueFactory->createForTest(
            attributeId: $attributeId,
            value: $value,
        );
    }
}
