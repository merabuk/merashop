<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Product;

use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Exception\Product\InvalidProductImageItemException;
use App\Catalog\Domain\ValueObject\Product\ImageCollection;
use App\Tests\Catalog\Support\ProductImageMother;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;

final class ImageCollectionTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    public function testItCreatesValidImageCollection(): void
    {
        $images = self::getValidImages();

        $vo = ImageCollection::fromArray($images);

        self::assertCount(count($images), $vo);
        self::assertInstanceOf(Traversable::class, $vo->getIterator());
        foreach ($vo->all() as $image) {
            self::assertInstanceOf(ProductImage::class, $image);
        }
    }

    public function testItProvidesEqualityCheck(): void
    {
        $images = self::getValidImages();

        $this->assertArrayVOProvidesEqualityCheck(
            className: ImageCollection::class,
            value: $images,
            shuffledValue: array_reverse($images),
            anotherValue: [$images[0]],
        );
    }

    #[DataProvider('invalidImageCollectionProvider')]
    public function testThrowsExceptionOnInvalidInput(array $invalidValue, string $exceptionClass): void
    {
        $this->expectException($exceptionClass);
        ImageCollection::fromArray($invalidValue);
    }

    public static function invalidImageCollectionProvider(): iterable
    {
        yield 'invalid type in array' => [
            'invalidValue' => [new stdClass()],
            'exceptionClass' => InvalidProductImageItemException::class,
        ];
    }

    public function testItAddsNewImage(): void
    {
        $vo = ImageCollection::empty();

        $newVo = $vo->add(ProductImageMother::createWithData());

        self::assertCount(0, $vo);
        self::assertCount(1, $newVo);
        self::assertFalse($newVo->equals($vo));
    }

    /**
     * @return ProductImage[]
     */
    private static function getValidImages(): array
    {
        $data = [
            [
                'id' => 123,
                'ulid' => '01KKTVY7D6D7S1BCSBB3GQA8B3',
            ],
            [
                'id' => 456,
                'ulid' => '01KKTVY7D6D7S1BCSBB3GQA8B4',
            ],
            [
                'id' => 789,
                'ulid' => '01KKTVY7D6D7S1BCSBB3GQA8B5',
            ],
        ];

        return array_map(fn (array $i) => ProductImageMother::createWithData(ulid: $i['ulid'], id: $i['id']), $data);
    }
}
