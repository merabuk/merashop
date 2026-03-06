<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateProduct;

use App\Catalog\Application\Exception\Product\UpdateProductException;
use App\Catalog\Application\Service\ProductDataFactory;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\Product\ProductAlreadyExistsException;
use App\Catalog\Domain\Exception\Product\ProductNotFoundException;
use App\Catalog\Domain\Repository\ProductReadRepositoryInterface;
use App\Catalog\Domain\Repository\ProductWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Price;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
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
        private ProductDataFactory $productDataFactory,
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
     */
    public function __invoke(UpdateProductCommand $command): int
    {
        try {
            $product = $this->readRepository->getById(Id::fromInt($command->id));

            if ($product->getVersion()->value() !== $command->version) {
                throw new ConcurrencyException();
            }

            $sku = Sku::fromString($command->sku);

            if (!$product->getSku()->equals($sku) && $this->readRepository->existsBySku($sku)) {
                throw new ProductAlreadyExistsException();
            }

            $categoryIds = $this->productDataFactory->prepareCategories($command->categoryIds);
            $attributeValues = $this->productDataFactory->prepareAttributes($command->attributeValues);

            $product->update(
                sku: $sku,
                price: new Price($command->priceAmount, $command->priceCurrency),
                status: Status::fromString($command->status),
                translations: Translations::fromArray($command->translations),
                updatedBy: AdminUlid::fromString($command->adminUlid),
                categoryIds: $categoryIds,
                attributeValues: $attributeValues
            );

            $product = $this->writeRepository->save($product);

            return $product->getId()->value();
        } catch (
            ConcurrencyException
            |OneOfAttributesNotFoundException
            |OneOfCategoriesNotFoundException
            |ProductAlreadyExistsException
            |ProductNotFoundException $e
        ) {
            throw $e;
        } catch (Throwable $e) {
            throw new UpdateProductException(message: 'Error during updating product', previous: $e);
        }
    }
}
