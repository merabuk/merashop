<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateProduct;

use App\Catalog\Application\Exception\Product\UpdateProductException;
use App\Catalog\Application\Service\Product\ProductApplicationFactoryInterface;
use App\Catalog\Application\Service\Product\ProductMediaManagerInterface;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\Product\ProductAlreadyExistsException;
use App\Catalog\Domain\Exception\Product\ProductNotFoundException;
use App\Catalog\Domain\Exception\TemporaryImage\OneOfTemporaryImagesNotFoundException;
use App\Catalog\Domain\Repository\ProductReadRepositoryInterface;
use App\Catalog\Domain\Repository\ProductWriteRepositoryInterface;
use App\Catalog\Domain\Service\Product\ProductValidatorInterface;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class UpdateProductHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProductReadRepositoryInterface $readRepository,
        private ProductValidatorInterface $productValidator,
        private ProductApplicationFactoryInterface $productFactory,
        private ProductMediaManagerInterface $productMediaManager,
        private ProductWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws ConcurrencyException
     * @throws OneOfAttributesNotFoundException
     * @throws OneOfCategoriesNotFoundException
     * @throws ProductAlreadyExistsException
     * @throws ProductNotFoundException
     * @throws UpdateProductException
     * @throws OneOfTemporaryImagesNotFoundException
     */
    public function __invoke(UpdateProductCommand $command): int
    {
        try {
            $product = $this->readRepository->getById(Id::fromInt($command->id));

            $newSku = Sku::fromString($command->sku);
            $categoryIds = $this->productFactory->mapCategoryIds($command->categoryIds);
            $attributeValues = $this->productFactory->mapAttributeIds($command->attributeValues);
            $temporaryImagesUlids = $this->productMediaManager->mapTemporaryImagesUlids($command->images, $product->getImages());
            $productImagesUlidsForDelete = $this->productMediaManager->mapProductImagesUlidsForDelete($command->images, $product->getImages());

            $this->productValidator->validateUpdate(
                product: $product,
                version: $command->version,
                newSku: $newSku,
                categoryIds: $categoryIds,
                attributeIds: $attributeValues,
                temporaryImageUlids: $temporaryImagesUlids,
                productImagesUlidsForDelete: $productImagesUlidsForDelete,
            );

            $this->productFactory->updateFromCommand($product, $command);

            $removedPaths = $this->productMediaManager->syncImagesForProduct(
                product: $product,
                imagesUlids: $command->images
            );

            $product = $this->writeRepository->save($product);

            $this->productMediaManager->deleteTemporaryImages($temporaryImagesUlids);
            // TODO: rework deleting images files through event (async)
            $this->productMediaManager->deleteProductImages($removedPaths);

            return $product->getId()->value();
        } catch (
            ConcurrencyException
            |OneOfAttributesNotFoundException
            |OneOfCategoriesNotFoundException
            |OneOfTemporaryImagesNotFoundException
            |ProductAlreadyExistsException
            |ProductNotFoundException $e
        ) {
            throw $e;
        } catch (Throwable $e) {
            throw new UpdateProductException(message: 'Error during updating product', previous: $e);
        }
    }
}
