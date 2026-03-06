<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateProduct;

use App\Catalog\Application\Exception\Product\CreateProductException;
use App\Catalog\Application\Service\ProductDataFactory;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\Product\ProductAlreadyExistsException;
use App\Catalog\Domain\Repository\ProductReadRepositoryInterface;
use App\Catalog\Domain\Repository\ProductWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Product\Price;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class CreateProductHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProductReadRepositoryInterface $readRepository,
        private UlidGeneratorInterface $ulidGenerator,
        private ProductDataFactory $productDataFactory,
        private ProductWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws CreateProductException
     * @throws OneOfAttributesNotFoundException
     * @throws OneOfCategoriesNotFoundException
     * @throws ProductAlreadyExistsException
     */
    public function __invoke(CreateProductCommand $command): int
    {
        try {
            $sku = Sku::fromString($command->sku);

            if ($this->readRepository->existsBySku($sku)) {
                throw new ProductAlreadyExistsException();
            }

            $ulid = $this->ulidGenerator->next();

            $categoryIds = $this->productDataFactory->prepareCategories($command->categoryIds);
            $attributeValues = $this->productDataFactory->prepareAttributes($command->attributeValues);

            $product = Product::create(
                ulid: Ulid::fromString($ulid),
                sku: $sku,
                price: new Price($command->priceAmount, $command->priceCurrency),
                status: Status::fromString($command->status),
                translations: Translations::fromArray($command->translations),
                createdBy: AdminUlid::fromString($command->adminUlid),
                categoryIds: $categoryIds,
                attributeValues: $attributeValues,
            );

            $product = $this->writeRepository->save($product);

            return $product->getId()->value();
        } catch (OneOfAttributesNotFoundException|OneOfCategoriesNotFoundException|ProductAlreadyExistsException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new CreateProductException(message: 'Error during creating product', previous: $e);
        }
    }
}
