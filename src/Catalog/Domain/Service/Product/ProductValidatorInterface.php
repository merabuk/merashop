<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Product;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\Product\ProductAlreadyExistsException;
use App\Catalog\Domain\Exception\TemporaryImage\OneOfTemporaryImagesNotFoundException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;

interface ProductValidatorInterface
{
    /**
     * @param CategoryId[]         $categoryIds
     * @param AttributeId[]        $attributeIds
     * @param TemporaryImageUlid[] $temporaryImagesUlids
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
        array $temporaryImagesUlids,
    ): void;

    /**
     * @param CategoryId[]         $categoryIds
     * @param AttributeId[]        $attributeIds
     * @param TemporaryImageUlid[] $temporaryImagesUlids
     *
     * @throws ConcurrencyException
     * @throws ProductAlreadyExistsException
     * @throws OneOfCategoriesNotFoundException
     * @throws OneOfAttributesNotFoundException
     * @throws OneOfTemporaryImagesNotFoundException
     */
    public function validateUpdate(
        Product $product,
        int $version,
        Sku $newSku,
        array $categoryIds,
        array $attributeIds,
        array $temporaryImagesUlids,
    ): void;
}
