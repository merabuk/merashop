<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Product;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Exception\Product\InvalidProductAttributeValueItemException;
use App\Catalog\Domain\ValueObject\Product\AttributeValueCollection;
use App\Tests\Catalog\Support\ProductAttributeValueMother;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;

final class AttributeValueCollectionTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    public function testItCreatesValidProductAttributeValueCollection(): void
    {
        $attributeValues = self::getValidAttributeValues();

        $vo = AttributeValueCollection::fromArray($attributeValues);

        self::assertCount(count($attributeValues), $vo);
        self::assertInstanceOf(Traversable::class, $vo->getIterator());
        foreach ($vo->all() as $pav) {
            self::assertInstanceOf(ProductAttributeValue::class, $pav);
        }
    }

    public function testItProvidesEqualityCheck(): void
    {
        $attributeValues = self::getValidAttributeValues();

        $this->assertArrayVOProvidesEqualityCheck(
            className: AttributeValueCollection::class,
            value: $attributeValues,
            shuffledValue: array_reverse($attributeValues),
            anotherValue: [$attributeValues[0]],
        );
    }

    #[DataProvider('invalidProductAttributeValueCollectionProvider')]
    public function testThrowsExceptionOnInvalidInput(array $invalidValue, string $exceptionClass): void
    {
        $this->expectException($exceptionClass);
        AttributeValueCollection::fromArray($invalidValue);
    }

    public static function invalidProductAttributeValueCollectionProvider(): iterable
    {
        yield 'invalid type in array' => [
            'invalidValue' => [new stdClass()],
            'exceptionClass' => InvalidProductAttributeValueItemException::class,
        ];
    }

    /**
     * @return ProductAttributeValue[]
     */
    private static function getValidAttributeValues(?array $attributeIds = null): array
    {
        $attributeIds ??= [123, 456, 789];

        return array_map(fn (int $id) => ProductAttributeValueMother::createWithData(attributeId: $id), $attributeIds);
    }
}
