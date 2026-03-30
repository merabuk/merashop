<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Exception\Product\InvalidProductImageItemException;
use App\Catalog\Domain\Exception\Product\ProductImagesMainImageException;
use App\Catalog\Domain\Exception\Product\ProductImageUniqueException;
use App\Catalog\Domain\Exception\ProductImage\InvalidProductImageUlidException;
use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageUlidException;
use App\Catalog\Domain\Repository\TemporaryImageReadRepositoryInterface;
use App\Catalog\Domain\Repository\TemporaryImageWriteRepositoryInterface;
use App\Catalog\Domain\Service\CatalogStorageInterface;
use App\Catalog\Domain\ValueObject\Product\ImageCollection;
use App\Catalog\Domain\ValueObject\ProductImage\MainImageFlag;
use App\Catalog\Domain\ValueObject\ProductImage\SortOrder;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid as ProductImageUlid;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use App\Shared\Domain\Exception\Services\Storage\FileStorageException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;

final readonly class ProductMediaManager implements ProductMediaManagerInterface
{
    public function __construct(
        private TemporaryImageReadRepositoryInterface $temporaryImageReadRepository,
        private TemporaryImageWriteRepositoryInterface $temporaryImageWriteRepository,
        private CatalogStorageInterface $storage,
    ) {
    }

    /**
     * @param string[] $imagesUlids
     *
     * @return TemporaryImageUlid[]
     *
     * @throws InvalidTemporaryImageUlidException
     */
    public function mapTemporaryImagesUlids(array $imagesUlids, ?ImageCollection $imageCollection = null): array
    {
        if (null !== $imageCollection) {
            $filteredUlids = array_filter($imagesUlids, fn (string $ulid) => null === $imageCollection->getByUlid($ulid));
        } else {
            $filteredUlids = $imagesUlids;
        }

        return array_values(array_map(fn (string $ulid) => TemporaryImageUlid::fromString($ulid), $filteredUlids));
    }

    /**
     * @param string[] $imagesUlids
     *
     * @return ProductImageUlid[]
     */
    public function mapProductImagesUlidsForDelete(array $imagesUlids, ImageCollection $imageCollection): array
    {
        $map = array_flip($imagesUlids);

        $productImageUlids = [];
        foreach ($imageCollection as $image) {
            if (!isset($map[$image->getUlid()->value()])) {
                $productImageUlids[] = $image->getUlid();
            }
        }

        return $productImageUlids;
    }

    /**
     * @param string[] $imagesUlids
     *
     * @throws FileStorageException
     * @throws InvalidProductImageItemException
     * @throws InvalidProductImageUlidException
     * @throws InvalidTemporaryImageUlidException
     * @throws ProductImagesMainImageException
     * @throws ProductImageUniqueException
     */
    public function activateImagesForProduct(Product $product, array $imagesUlids): void
    {
        if (empty($imagesUlids)) {
            return;
        }

        $temporaryImages = $this->temporaryImageReadRepository->findByUlids(
            ulids: $this->mapTemporaryImagesUlids($imagesUlids)
        );

        foreach ($temporaryImages as $index => $temporaryImage) {
            $productImage = $this->moveTemporaryImageToProduct(
                temporaryImage: $temporaryImage,
                sortOrder: $index,
                isMain: 0 === $index
            );

            $product->addImage($productImage);
        }

        $product->reorderImages($imagesUlids);
    }

    /**
     * @param string[] $imagesUlids
     *
     * @return RelativeFilePath[] removed paths
     *
     * @throws FileStorageException
     * @throws InvalidProductImageUlidException
     * @throws InvalidProductImageItemException
     * @throws InvalidTemporaryImageUlidException
     * @throws ProductImagesMainImageException
     * @throws ProductImageUniqueException
     */
    public function syncProductImages(Product $product, array $imagesUlids): array
    {
        $removedPaths = [];
        $toDelete = $this->mapProductImagesUlidsForDelete($imagesUlids, $product->getImages());

        foreach ($toDelete as $ulid) {
            $image = $product->getImages()->getByUlid($ulid);
            if ($image) {
                $removedPaths[] = $image->getPath();
                $product->removeImage($ulid);
            }
        }

        $newTemporaryUlids = $this->mapTemporaryImagesUlids($imagesUlids, $product->getImages());
        $temporaryImages = $this->temporaryImageReadRepository->findByUlids(ulids: $newTemporaryUlids);

        $sortOrder = $product->getImages()->count();

        foreach ($temporaryImages as $temporaryImage) {
            $productImage = $this->moveTemporaryImageToProduct(temporaryImage: $temporaryImage, sortOrder: $sortOrder);
            $product->addImage($productImage);
            ++$sortOrder;
        }

        $product->reorderImages($imagesUlids);

        return $removedPaths;
    }

    /**
     * @param TemporaryImageUlid[] $temporaryImagesUlids
     */
    public function deleteTemporaryImages(array $temporaryImagesUlids): void
    {
        $this->temporaryImageWriteRepository->deleteByUlids($temporaryImagesUlids);
    }

    /**
     * @param RelativeFilePath[] $removedPaths
     *
     * @throws FileStorageException
     */
    public function deleteProductImages(array $removedPaths): void
    {
        if (empty($removedPaths)) {
            return;
        }

        foreach ($removedPaths as $path) {
            $this->storage->delete($path);
        }
    }

    /**
     * @throws FileStorageException
     * @throws InvalidProductImageUlidException
     */
    private function moveTemporaryImageToProduct(
        TemporaryImage $temporaryImage,
        int $sortOrder,
        bool $isMain = false,
    ): ProductImage {
        $newPath = $this->storage->generateProductImageStoragePath($temporaryImage->getPath());

        $this->storage->move($temporaryImage->getPath()->value(), $newPath->value());

        return ProductImage::create(
            ulid: ProductImageUlid::fromString($temporaryImage->getUlid()->value()),
            path: $newPath,
            sortOrder: SortOrder::fromInt($sortOrder),
            isMain: MainImageFlag::fromBool($isMain)
        );
    }
}
