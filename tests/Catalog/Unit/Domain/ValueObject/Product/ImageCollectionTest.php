<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Product;

use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Exception\Product\InvalidProductImageItemException;
use App\Catalog\Domain\Exception\Product\ProductImagesMainImageException;
use App\Catalog\Domain\Exception\Product\ProductImageUniqueException;
use App\Catalog\Domain\ValueObject\Product\ImageCollection;
use App\Tests\Catalog\Support\ProductImageMother;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Traversable;

final class ImageCollectionTest extends BaseUnitTest
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
        yield 'has duplicate image' => [
            'invalidValue' => [
                ProductImageMother::createWithData(isMain: true),
                ProductImageMother::createWithData(),
            ],
            'exceptionClass' => ProductImageUniqueException::class,
        ];
        yield 'does not have main image' => [
            'invalidValue' => [ProductImageMother::createWithData(isMain: false)],
            'exceptionClass' => ProductImagesMainImageException::class,
        ];
        yield 'has more than one main image' => [
            'invalidValue' => [
                ProductImageMother::createWithData(
                    ulid: '01KKTVY7D6D7S1BCSBB3GQA8B3',
                    isMain: true
                ),
                ProductImageMother::createWithData(
                    ulid: '01KKTVY7D6D7S1BCSBB3GQA8B4',
                    isMain: true
                ),
            ],
            'exceptionClass' => ProductImagesMainImageException::class,
        ];
    }

    public function testItGetsByUlid(): void
    {
        $images = self::getValidImages();
        $vo = ImageCollection::fromArray($images);

        foreach ($images as $image) {
            $foundImage = $vo->getByUlid($image->getUlid()->value());
            self::assertNotNull($foundImage);
            self::assertSame($image, $foundImage);
        }
    }

    public function testItAddsNewImage(): void
    {
        $vo = ImageCollection::empty();

        $newVo = $vo->add(ProductImageMother::createWithData());

        self::assertCount(0, $vo);
        self::assertCount(1, $newVo);
        self::assertFalse($newVo->equals($vo));
    }

    public function testItRemovesImageByUlid(): void
    {
        $images = self::getValidImages();

        $vo = ImageCollection::fromArray($images);

        $ulid = $images[0]->getUlid();
        $newVo = $vo->remove($ulid);

        self::assertCount(3, $vo);
        self::assertCount(2, $newVo);
        self::assertFalse($vo->equals($newVo));
        self::assertNotNull($vo->getByUlid($ulid->value()));
        self::assertNull($newVo->getByUlid($ulid->value()));
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
                'isMain' => true,
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

        return array_map(fn (array $i) => ProductImageMother::createWithData(
            ulid: $i['ulid'],
            isMain: $i['isMain'] ?? false,
            id: $i['id']
        ), $data);
    }
}
