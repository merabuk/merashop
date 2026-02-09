<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryIdException;
use App\Catalog\Domain\Exception\Product\InvalidProductIdException;
use App\Catalog\Domain\Exception\Product\InvalidProductPriceAmountException;
use App\Catalog\Domain\Exception\Product\InvalidProductPriceCurrencyException;
use App\Catalog\Domain\Exception\Product\InvalidProductSkuException;
use App\Catalog\Domain\Exception\Product\InvalidProductUlidException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Price;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategory;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProduct;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductAttributeValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductTranslation;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

/**
 * @implements MapperInterface<Product, OrmProduct>
 */
class ProductMapper implements MapperInterface
{
    use TypeCheckTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     */
    public function toDoctrineOrm(object $domain): OrmProduct
    {
        $this->assertIsType(Product::class, $domain);
        /** @var Product $domain */

        $orm = new OrmProduct();
        $this->mapToExistingOrm($domain, $orm);

        return $orm;
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidProductPriceAmountException
     * @throws InvalidProductIdException
     * @throws InvalidAttributeIdException
     * @throws InvalidProductUlidException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidProductPriceCurrencyException
     * @throws InvalidCategoryIdException
     * @throws InvalidProductSkuException
     */
    public function fromDoctrineOrm(object $orm): Product
    {
        $this->assertIsType(OrmProduct::class, $orm);
        /** @var OrmProduct $orm */

        $translations = [];
        foreach ($orm->translations as $translation) {
            $translations[$translation->locale] = [
                'name' => $translation->name,
                'description' => $translation->description,
            ];
        }

        $categoryIds = [];
        foreach ($orm->categories as $category) {
            $categoryIds[] = CategoryId::fromInt($category->id);
        }

        $productId = Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class));

        $attributeValues = [];
        foreach ($orm->attributeValues as $ormValue) {
            $value = match ($ormValue->attribute->type) {
                TypeEnum::String, TypeEnum::Select => $ormValue->valueString,
                TypeEnum::Int => $ormValue->valueInt,
                TypeEnum::Boolean => $ormValue->valueBoolean,
            };

            $attributeValues[] = new ProductAttributeValue(
                id: $ormValue->id,
                productId: $productId,
                attributeId: AttributeId::fromInt($ormValue->attribute->id),
                value: $value
            );
        }

        return new Product(
            id: $productId,
            ulid: Ulid::fromString($orm->ulid),
            sku: Sku::fromString($orm->sku),
            price: new Price($orm->priceAmount, $orm->priceCurrency),
            status: Status::fromEnum($orm->status),
            translations: $translations,
            categoryIds: $categoryIds,
            attributeValues: $attributeValues
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(Product::class, $domain);
        $this->assertIsType(OrmProduct::class, $orm);
        /** @var Product $domain */
        /** @var OrmProduct $orm */

        $orm->ulid = $domain->getUlid()->value();
        $orm->sku = $domain->getSku()->value();
        $orm->priceAmount = $domain->getPrice()->getAmount();
        $orm->priceCurrency = $domain->getPrice()->getCurrency();
        $orm->status = $domain->getStatus()->value();

        // Map categories
        $orm->categories->clear();
        foreach ($domain->getCategoryIds() as $categoryId) {
            $orm->categories->add($this->entityManager->getReference(OrmCategory::class, $categoryId->value()));
        }

        // Map translations
        $currentTranslations = [];
        foreach ($orm->translations as $translation) {
            $currentTranslations[$translation->locale] = $translation;
        }

        foreach ($domain->getTranslations() as $locale => $data) {
            if (isset($currentTranslations[$locale])) {
                $currentTranslations[$locale]->name = $data['name'];
                $currentTranslations[$locale]->description = $data['description'] ?? null;
                unset($currentTranslations[$locale]);
            } else {
                $translation = new OrmProductTranslation();
                $translation->product = $orm;
                $translation->locale = $locale;
                $translation->name = $data['name'];
                $translation->description = $data['description'] ?? null;
                $orm->translations->add($translation);
            }
        }

        foreach ($currentTranslations as $translation) {
            $orm->translations->removeElement($translation);
        }

        // Map attribute values
        $currentValues = [];
        foreach ($orm->attributeValues as $ormValue) {
            $currentValues[$ormValue->attribute->id] = $ormValue;
        }

        foreach ($domain->getAttributeValues() as $domainValue) {
            $attributeId = $domainValue->getAttributeId()->value();
            if (isset($currentValues[$attributeId])) {
                $ormValue = $currentValues[$attributeId];
                $this->setOrmAttributeValue($ormValue, $domainValue->getValue());
                unset($currentValues[$attributeId]);
            } else {
                $ormValue = new OrmProductAttributeValue();
                $ormValue->product = $orm;
                $ormValue->attribute = $this->entityManager->getReference(OrmAttribute::class, $attributeId);
                $this->setOrmAttributeValue($ormValue, $domainValue->getValue());
                $orm->attributeValues->add($ormValue);
            }
        }

        foreach ($currentValues as $ormValue) {
            $orm->attributeValues->removeElement($ormValue);
        }
    }

    private function setOrmAttributeValue(OrmProductAttributeValue $ormValue, mixed $value): void
    {
        $ormValue->valueString = null;
        $ormValue->valueInt = null;
        $ormValue->valueBoolean = null;
        $ormValue->valueJson = null;

        if (is_string($value)) {
            $ormValue->valueString = $value;
        } elseif (is_int($value)) {
            $ormValue->valueInt = $value;
        } elseif (is_bool($value)) {
            $ormValue->valueBoolean = $value;
        } elseif (is_array($value)) {
            $ormValue->valueJson = $value;
        }
    }
}
