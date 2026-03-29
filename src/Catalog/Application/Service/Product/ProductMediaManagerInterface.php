<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\ValueObject\Product\ImageCollection;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid as ProductImageUlid;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use App\Shared\Domain\Exception\Services\Storage\FileStorageException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;

interface ProductMediaManagerInterface
{
    /**
     * @param string[] $imagesUlids
     *
     * @return TemporaryImageUlid[]
     */
    public function mapTemporaryImagesUlids(array $imagesUlids, ?ImageCollection $imageCollection = null): array;

    /**
     * @param string[] $imagesUlids
     *
     * @return ProductImageUlid[]
     */
    public function mapProductImagesUlidsForDelete(array $imagesUlids, ImageCollection $imageCollection): array;

    /**
     * @param string[] $imagesUlids
     *
     * @throws FileStorageException
     */
    public function activateImagesForProduct(Product $product, array $imagesUlids): void;

    /**
     * @param string[] $imagesUlids
     *
     * @return RelativeFilePath[] removed paths
     *
     * @throws FileStorageException
     */
    public function syncImagesForProduct(Product $product, array $imagesUlids): array;

    /**
     * @param TemporaryImageUlid[] $temporaryImagesUlids
     */
    public function deleteTemporaryImages(array $temporaryImagesUlids): void;

    /**
     * @param RelativeFilePath[] $removedPaths
     *
     * @throws FileStorageException
     */
    public function deleteProductImages(array $removedPaths): void;
}
