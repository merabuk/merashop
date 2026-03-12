<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Exception\ProductImage\InvalidProductImageUlidException;
use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageUlidException;
use App\Catalog\Domain\Repository\TemporaryImageReadRepositoryInterface;
use App\Catalog\Domain\Repository\TemporaryImageWriteRepositoryInterface;
use App\Catalog\Domain\Service\CatalogStorageInterface;
use App\Catalog\Domain\ValueObject\ProductImage\MainImageFlag;
use App\Catalog\Domain\ValueObject\ProductImage\SortOrder;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid as ProductImageUlid;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use App\Shared\Domain\Exception\Services\Storage\FileStorageException;

final readonly class ProductMediaManager implements ProductMediaManagerInterface
{
    public function __construct(
        private TemporaryImageReadRepositoryInterface $temporaryImageReadRepository,
        private TemporaryImageWriteRepositoryInterface $temporaryImageWriteRepository,
        private CatalogStorageInterface $storage,
    ) {
    }

    /**
     * @param string[] $temporaryImagesUlids
     *
     * @return TemporaryImageUlid[]
     *
     * @throws InvalidTemporaryImageUlidException
     */
    public function mapTemporaryImagesUlids(array $temporaryImagesUlids): array
    {
        return array_map(fn (string $ulid) => TemporaryImageUlid::fromString($ulid), $temporaryImagesUlids);
    }

    /**
     * @param TemporaryImageUlid[] $temporaryImagesUlids
     *
     * @throws FileStorageException
     * @throws InvalidProductImageUlidException
     */
    public function activateImagesForProduct(Product $product, array $temporaryImagesUlids): void
    {
        if (empty($temporaryImagesUlids)) {
            return;
        }

        $temporaryImages = $this->temporaryImageReadRepository->findByUlids($temporaryImagesUlids);

        $orderMap = array_flip(array_map(fn (TemporaryImageUlid $u) => $u->value(), $temporaryImagesUlids));

        usort($temporaryImages, fn (TemporaryImage $a, TemporaryImage $b) => ($orderMap[$a->getUlid()->value()] ?? 999) <=> ($orderMap[$b->getUlid()->value()] ?? 999)
        );

        foreach ($temporaryImages as $index => $tempImage) {
            $newPath = $this->storage->generateProductImageStoragePath($tempImage->getPath());

            $this->storage->move($tempImage->getPath()->value(), $newPath->value());

            $product->addImage(ProductImage::create(
                ulid: ProductImageUlid::fromString($tempImage->getUlid()->value()),
                path: $newPath,
                sortOrder: SortOrder::fromInt($index),
                isMain: MainImageFlag::fromBool(0 === $index)
            ));
        }
    }

    /**
     * @param TemporaryImageUlid[] $temporaryImagesUlids
     */
    public function deleteTemporaryImages(array $temporaryImagesUlids): void
    {
        $this->temporaryImageWriteRepository->deleteByUlids($temporaryImagesUlids);
    }
}
