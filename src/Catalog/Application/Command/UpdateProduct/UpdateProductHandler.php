<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateProduct;

use App\Catalog\Application\Exception\Product\UpdateProductException;
use App\Catalog\Application\Service\ProductDataFactory;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Exception\Product\ProductNotFoundException;
use App\Catalog\Domain\Repository\ProductReadRepositoryInterface;
use App\Catalog\Domain\Repository\ProductWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Price;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

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
     * @throws OneOfAttributesNotFoundException
     * @throws OneOfCategoriesNotFoundException
     * @throws ProductNotFoundException
     * @throws UpdateProductException
     */
    public function __invoke(UpdateProductCommand $command): int
    {
        try {
            $product = $this->readRepository->getById(Id::fromInt($command->id));

            $categoryIds = $this->productDataFactory->prepareCategories($command->categoryIds);
            $attributeValues = $this->productDataFactory->prepareAttributes($command->attributeValues);

            $product->update(
                sku: Sku::fromString($command->sku),
                price: new Price($command->priceAmount, $command->priceCurrency),
                status: Status::fromString($command->status),
                translations: Translations::fromArray($command->translations),
                categoryIds: $categoryIds,
                attributeValues: $attributeValues
            );

            $product = $this->writeRepository->save($product);

            return $product->getId()->value();
        } catch (InvalidCatalogValueObjectException|InvalidLocaleException $e) {
            throw new UpdateProductException(message: 'Error during updating product', previous: $e);
        }
    }
}
