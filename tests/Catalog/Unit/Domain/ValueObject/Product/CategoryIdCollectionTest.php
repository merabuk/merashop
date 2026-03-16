<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Product;

use App\Catalog\Domain\Exception\Product\InvalidProductCategoryIdItemException;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\CategoryIdCollection;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;

final class CategoryIdCollectionTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    public function testItCreatesValidCategoryIdCollection(): void
    {
        $categoryIds = self::getValidCategoryIds();

        $vo = CategoryIdCollection::fromArray($categoryIds);

        self::assertCount(count($categoryIds), $vo);
        self::assertInstanceOf(Traversable::class, $vo->getIterator());
        foreach ($vo->all() as $categoryId) {
            self::assertInstanceOf(CategoryId::class, $categoryId);
        }
    }

    public function testItProvidesEqualityCheck(): void
    {
        $categoryIds = self::getValidCategoryIds();

        $this->assertArrayVOProvidesEqualityCheck(
            className: CategoryIdCollection::class,
            value: $categoryIds,
            shuffledValue: array_reverse($categoryIds),
            anotherValue: [$categoryIds[0]],
        );
    }

    #[DataProvider('invalidCategoryIdCollectionProvider')]
    public function testThrowsExceptionOnInvalidInput(array $invalidValue, string $exceptionClass): void
    {
        $this->expectException($exceptionClass);
        CategoryIdCollection::fromArray($invalidValue);
    }

    public static function invalidCategoryIdCollectionProvider(): iterable
    {
        yield 'invalid type in array' => [
            'invalidValue' => [new stdClass()],
            'exceptionClass' => InvalidProductCategoryIdItemException::class,
        ];
    }

    /**
     * @return CategoryId[]
     */
    private static function getValidCategoryIds(?array $categoryIds = null): array
    {
        $categoryIds ??= [123, 456, 789];

        return array_map(fn (int $id) => CategoryId::fromInt($id), $categoryIds);
    }
}
