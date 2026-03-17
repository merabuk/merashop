<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Product;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\Product\ProductAlreadyExistsException;
use App\Catalog\Domain\Exception\TemporaryImage\OneOfTemporaryImagesNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Repository\ProductReadRepositoryInterface;
use App\Catalog\Domain\Repository\TemporaryImageReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;

final readonly class ProductValidator implements ProductValidatorInterface
{
    public function __construct(
        private ProductReadRepositoryInterface $productReadRepository,
        private CategoryReadRepositoryInterface $categoryReadRepository,
        private AttributeReadRepositoryInterface $attributeReadRepository,
        private TemporaryImageReadRepositoryInterface $temporaryImageReadRepository,
    ) {
    }

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
    ): void {
        if ($this->productReadRepository->existsBySku($sku)) {
            throw ProductAlreadyExistsException::becauseSkuAlreadyExists($sku->value());
        }

        $this->categoryReadRepository->assertAllExistByIds($categoryIds);
        $this->attributeReadRepository->assertAllExistByIds($attributeIds);

        $this->temporaryImageReadRepository->assertAllExistByUlidAndContext($temporaryImageUlids, ContextEnum::ProductMain);
    }

    public function validateUpdate(
        Product $product,
        int $version,
        Sku $newSku,
        array $categoryIds,
        array $attributeIds,
        array $temporaryImageUlids,
    ): void {
        if ($product->getVersion()->value() !== $version) {
            throw new ConcurrencyException();
        }

        if (!$product->getSku()->equals($newSku) && $this->productReadRepository->existsBySku($newSku)) {
            throw new ProductAlreadyExistsException();
        }

        $this->categoryReadRepository->assertAllExistByIds($categoryIds);
        $this->attributeReadRepository->assertAllExistByIds($attributeIds);

        $this->temporaryImageReadRepository->assertAllExistByUlidAndContext($temporaryImageUlids, ContextEnum::ProductMain);
    }
}
