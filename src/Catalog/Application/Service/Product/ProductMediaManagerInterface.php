<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use App\Shared\Domain\Exception\Services\Storage\FileStorageException;

interface ProductMediaManagerInterface
{
    /**
     * @param string[] $temporaryImagesUlids
     *
     * @return TemporaryImageUlid[]
     */
    public function mapTemporaryImagesUlids(array $temporaryImagesUlids): array;

    /**
     * @param TemporaryImageUlid[] $temporaryImagesUlids
     *
     * @throws FileStorageException
     */
    public function activateImagesForProduct(Product $product, array $temporaryImagesUlids): void;

    /**
     * @param TemporaryImageUlid[] $temporaryImagesUlids
     */
    public function deleteTemporaryImages(array $temporaryImagesUlids): void;
}
