<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateProduct;

use App\Catalog\Application\Exception\Product\CreateProductException;
use App\Catalog\Application\Service\ProductApplicationFactoryInterface;
use App\Catalog\Application\Service\ProductMediaManagerInterface;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\Product\ProductAlreadyExistsException;
use App\Catalog\Domain\Exception\TemporaryImage\OneOfTemporaryImagesNotFoundException;
use App\Catalog\Domain\Repository\ProductWriteRepositoryInterface;
use App\Catalog\Domain\Service\Product\ProductValidatorInterface;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class CreateProductHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProductValidatorInterface $productValidator,
        private UlidGeneratorInterface $ulidGenerator,
        private ProductApplicationFactoryInterface $productFactory,
        private ProductMediaManagerInterface $productMediaManager,
        private ProductWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws CreateProductException
     * @throws OneOfAttributesNotFoundException
     * @throws OneOfCategoriesNotFoundException
     * @throws OneOfTemporaryImagesNotFoundException
     * @throws ProductAlreadyExistsException
     */
    public function __invoke(CreateProductCommand $command): int
    {
        try {
            $sku = Sku::fromString($command->sku);
            $categoryIds = $this->productFactory->mapCategoryIds($command->categoryIds);
            $attributeIds = $this->productFactory->mapAttributeIds($command->attributeValues);
            $temporaryImagesUlids = $this->productMediaManager->mapTemporaryImagesUlids($command->images);

            $this->productValidator->validateCreation(
                sku: $sku,
                categoryIds: $categoryIds,
                attributeIds: $attributeIds,
                temporaryImageUlids: $temporaryImagesUlids
            );

            $ulid = $this->ulidGenerator->next();

            $product = $this->productFactory->createFromCommand($command, $ulid);

            $this->productMediaManager->activateImagesForProduct($product, $temporaryImagesUlids);

            $product = $this->writeRepository->save($product);

            $this->productMediaManager->deleteTemporaryImages($temporaryImagesUlids);

            return $product->getId()->value();
        } catch (
            OneOfAttributesNotFoundException
            |OneOfCategoriesNotFoundException
            |OneOfTemporaryImagesNotFoundException
            |ProductAlreadyExistsException $e
        ) {
            throw $e;
        } catch (Throwable $e) {
            throw new CreateProductException(message: 'Error during creating product', previous: $e);
        }
    }
}
