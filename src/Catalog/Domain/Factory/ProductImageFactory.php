<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Factory;

use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Exception\ProductImage\InvalidProductImageUlidException;
use App\Catalog\Domain\Factory\Contract\ProductImageFactoryInterface;
use App\Catalog\Domain\ValueObject\ProductImage\MainImageFlag;
use App\Catalog\Domain\ValueObject\ProductImage\SortOrder;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;

final readonly class ProductImageFactory implements ProductImageFactoryInterface
{
    /**
     * @throws InvalidRelativePathException
     * @throws InvalidProductImageUlidException
     */
    public function createForTest(
        string $ulid,
        string $path,
        int $sortOrder,
        bool $isMain,
    ): ProductImage {
        return new ProductImage(
            ulid: Ulid::fromString($ulid),
            path: RelativeFilePath::fromString($path),
            sortOrder: SortOrder::fromInt($sortOrder),
            isMain: MainImageFlag::fromBool($isMain),
        );
    }
}
