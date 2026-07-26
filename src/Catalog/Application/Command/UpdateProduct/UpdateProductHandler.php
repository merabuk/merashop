<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateProduct;

use App\Catalog\Application\Exception\Product\UpdateProductException;
use App\Catalog\Application\Service\Product\ProductApplicationFactoryInterface;
use App\Catalog\Application\Service\Product\ProductMediaManagerInterface;
use App\Catalog\Domain\Event\ProductImagesRemovedDomainEvent;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\Product\ProductAlreadyExistsException;
use App\Catalog\Domain\Exception\Product\ProductImagesEmptyException;
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
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use RuntimeException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
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
        private MessageBusInterface $eventBus,
    ) {
    }

    /**
     * @throws ConcurrencyException
     * @throws OneOfAttributesNotFoundException
     * @throws OneOfCategoriesNotFoundException
     * @throws OneOfTemporaryImagesNotFoundException
     * @throws ProductAlreadyExistsException
     * @throws ProductImagesEmptyException
     * @throws ProductNotFoundException
     * @throws UpdateProductException
     */
    public function __invoke(UpdateProductCommand $command): int
    {
        try {
            $product = $this->readRepository->getById(Id::fromInt($command->id));

            $newSku = Sku::fromString($command->sku);
            $categoryIds = $this->productFactory->mapCategoryIds(categoryIds: $command->categoryIds);
            $attributeIds = $this->productFactory->mapAttributeIds(attributeValues: $command->attributeValues);
            $temporaryImagesUlids = $this->productMediaManager->mapTemporaryImagesUlids(
                imagesUlids: $command->images,
                imageCollection: $product->getImages());
            $productImagesUlidsForDelete = $this->productMediaManager->mapProductImagesUlidsForDelete(
                imagesUlids: $command->images,
                imageCollection: $product->getImages()
            );

            $this->productValidator->validateUpdate(
                product: $product,
                version: $command->version,
                newSku: $newSku,
                categoryIds: $categoryIds,
                attributeIds: $attributeIds,
                temporaryImageUlids: $temporaryImagesUlids,
                productImagesUlidsForDelete: $productImagesUlidsForDelete,
            );

            $this->productFactory->updateFromCommand($product, $command);

            $removedPaths = $this->productMediaManager->syncProductImages(
                product: $product,
                imagesUlids: $command->images
            );

            $product = $this->writeRepository->save($product);

            $this->productMediaManager->deleteTemporaryImages($temporaryImagesUlids);

            if (!empty($removedPaths)) {
                $this->eventBus->dispatch(new ProductImagesRemovedDomainEvent(
                    productImagePaths: array_map(fn (RelativeFilePath $path) => $path->value(), $removedPaths),
                ));
            }

            return $product->getId()?->value() ?? throw new RuntimeException('Product id is null');
        } catch (
            ConcurrencyException
            |OneOfAttributesNotFoundException
            |OneOfCategoriesNotFoundException
            |OneOfTemporaryImagesNotFoundException
            |ProductAlreadyExistsException
            |ProductImagesEmptyException
            |ProductNotFoundException $e
        ) {
            throw $e;
        } catch (Throwable $e) {
            throw new UpdateProductException(message: 'Error during updating product', previous: $e);
        }
    }
}
