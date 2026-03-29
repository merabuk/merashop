<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Product;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\Product\ProductAlreadyExistsException;
use App\Catalog\Domain\Exception\Product\ProductImagesEmptyException;
use App\Catalog\Domain\Exception\TemporaryImage\OneOfTemporaryImagesNotFoundException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid as ProductImageUlid;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;

interface ProductValidatorInterface
{
    /**
     * @param CategoryId[]         $categoryIds
     * @param AttributeId[]        $attributeIds
     * @param TemporaryImageUlid[] $temporaryImageUlids
     *
     * @throws ProductAlreadyExistsException
     * @throws OneOfCategoriesNotFoundException
     * @throws OneOfAttributesNotFoundException
     * @throws OneOfTemporaryImagesNotFoundException
     */
    public function validateCreation(
        Sku $sku,
        array $categoryIds,
        array $attributeIds,
        array $temporaryImageUlids,
    ): void;

    /**
     * @param CategoryId[]         $categoryIds
     * @param AttributeId[]        $attributeIds
     * @param TemporaryImageUlid[] $temporaryImageUlids
     * @param ProductImageUlid[]   $productImagesUlidsForDelete
     *
     * @throws ConcurrencyException
     * @throws OneOfCategoriesNotFoundException
     * @throws OneOfAttributesNotFoundException
     * @throws OneOfTemporaryImagesNotFoundException
     * @throws ProductAlreadyExistsException
     * @throws ProductImagesEmptyException
     */
    public function validateUpdate(
        Product $product,
        int $version,
        Sku $newSku,
        array $categoryIds,
        array $attributeIds,
        array $temporaryImageUlids,
        array $productImagesUlidsForDelete,
    ): void;
}
