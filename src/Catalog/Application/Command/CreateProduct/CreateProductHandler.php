<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateProduct;

use App\Catalog\Application\Exception\Product\CreateProductException;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Repository\ProductWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Price;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class CreateProductHandler implements CommandHandlerInterface
{
    public function __construct(
        private UlidGeneratorInterface $ulidGenerator,
        private CategoryReadRepositoryInterface $categoryReadRepository,
        private AttributeReadRepositoryInterface $attributeReadRepository,
        private ProductWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws CreateProductException
     * @throws OneOfAttributesNotFoundException
     * @throws OneOfCategoriesNotFoundException
     */
    public function __invoke(CreateProductCommand $command): int
    {
        try {
            $ulid = $this->ulidGenerator->next();

            $categoryIds = [];
            foreach ($command->categoryIds as $categoryId) {
                $categoryIds[] = CategoryId::fromInt($categoryId);
            }
            $this->categoryReadRepository->assertAllExistByIds($categoryIds);

            $attributeIds = [];
            $attributeValues = [];
            foreach ($command->attributeValues as $attributeValue) {
                $attributeId = AttributeId::fromInt($attributeValue['attributeId']);
                $attributeIds[] = $attributeId;
                $attributeValues[] = ProductAttributeValue::createWithRawValue(
                    attributeId: $attributeId,
                    value: $attributeValue['value'],
                );
            }
            $this->attributeReadRepository->assertAllExistByIds($attributeIds);

            $product = Product::create(
                ulid: Ulid::fromString($ulid),
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
            throw new CreateProductException(message: 'Error during creating attribute', previous: $e);
        }
    }
}
