<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\ValueObject\ProductImage\MainImageFlag;
use App\Catalog\Domain\ValueObject\ProductImage\SortOrder;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use App\Tests\Catalog\Support\ProductImageMother;
use PHPUnit\Framework\TestCase;

final class ProductImageTest extends TestCase
{
    public function testItCreatesProductImage(): void
    {
        $ulid = Ulid::fromString(ProductImageMother::DEFAULT_ULID);
        $path = RelativeFilePath::fromString('path/to/file/image.jpg');
        $sortOrder = SortOrder::fromInt(1);
        $isMain = MainImageFlag::fromBool(true);

        $productImage = ProductImage::create(
            ulid: $ulid,
            path: $path,
            sortOrder: $sortOrder,
            isMain: $isMain,
        );

        self::assertNull($productImage->getId());
        self::assertTrue($productImage->getUlid()->equals($ulid));
        self::assertTrue($productImage->getPath()->equals($path));
        self::assertTrue($productImage->getSortOrder()->equals($sortOrder));
        self::assertTrue($productImage->isMain()->equals($isMain));
    }

    public function testItSetsAsMain(): void
    {
        $productImage = ProductImageMother::createWithData(isMain: false);

        $productImage->setAsMain();

        self::assertTrue($productImage->isMain()->isTrue());
    }

    public function testIsUnsetsMain(): void
    {
        $productImage = ProductImageMother::createWithData(isMain: true);

        $productImage->unsetMain();

        self::assertFalse($productImage->isMain()->isTrue());
    }

    public function testItUpdatesSortOrder(): void
    {
        $productImage = ProductImageMother::createWithData(sortOrder: 1);
        $newSortOrder = SortOrder::fromInt(2);

        $productImage->updateSortOrder($newSortOrder);

        self::assertTrue($productImage->getSortOrder()->equals($newSortOrder));
    }
}
