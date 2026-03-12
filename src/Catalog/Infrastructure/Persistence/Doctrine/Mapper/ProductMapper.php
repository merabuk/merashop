<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Category\InvalidCategoryIdException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Exception\Product\ProductPriceUniqueException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Id as ProductId;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid as ProductUlid;
use App\Catalog\Domain\ValueObject\Product\Version;
use App\Catalog\Domain\ValueObject\ProductAttribute\ArrayValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\BooleanValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Id as ProductAttributeValueId;
use App\Catalog\Domain\ValueObject\ProductAttribute\IntegerValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\StringValue;
use App\Catalog\Domain\ValueObject\ProductImage\Id as ProductImageId;
use App\Catalog\Domain\ValueObject\ProductImage\MainImageFlag;
use App\Catalog\Domain\ValueObject\ProductImage\SortOrder;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid as ProductImageUlid;
use App\Catalog\Domain\ValueObject\ProductPrice\Id as ProductPriceId;
use App\Catalog\Domain\ValueObject\ProductPrice\Price;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Catalog\Domain\ValueObject\ProductPrice\Type;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidFrom;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidTo;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategory;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProduct;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductAttributeValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductImage;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductPrice;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductTranslation;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use App\Shared\Infrastructure\Persistence\Doctrine\Interface\ProxyReferenceProviderInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;
use InvalidArgumentException;

/**
 * @implements MapperInterface<Product, OrmProduct>
 */
class ProductMapper implements MapperInterface
{
    use TypeCheckTrait;

    public function __construct(
        private readonly ProxyReferenceProviderInterface $referenceProvider,
        // TODO: move private methods to separate mappers by model type
    ) {
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmProduct
    {
        $this->assertIsType(Product::class, $domain);
        /* @var Product $domain */

        $orm = new OrmProduct();

        $orm->ulid = $domain->getUlid()->value();
        $orm->version = $domain->getVersion()->value();
        $orm->createdBy = $domain->getCreatedBy()->value();

        $this->mapToExistingOrm($domain, $orm);

        return $orm;
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws InvalidRelativePathException
     * @throws ProductPriceUniqueException
     */
    public function fromDoctrineOrm(object $orm): Product
    {
        $this->assertIsType(OrmProduct::class, $orm);
        /** @var OrmProduct $orm */
        $productId = ProductId::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class));

        $prices = $this->mapPricesFromOrmToDomain($orm);
        $categoryIds = $this->mapCategoriesFromOrmToDomain($orm);
        $translations = $this->mapTranslationsFromOrmToDomain($orm);
        $attributeValues = $this->mapAttributesFromOrmToDomain($orm);
        $images = $this->mapImagesFromOrmToDomain($orm);

        return new Product(
            id: $productId,
            ulid: ProductUlid::fromString($orm->ulid),
            sku: Sku::fromString($orm->sku),
            status: Status::fromEnum($orm->status),
            translations: $translations,
            version: Version::fromInt($orm->version),
            createdBy: AdminUlid::fromString($orm->createdBy),
            prices: $prices,
            updatedBy: $orm->updatedBy ? AdminUlid::fromString($orm->updatedBy) : null,
            categoryIds: $categoryIds,
            attributeValues: $attributeValues,
            images: $images,
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(Product::class, $domain);
        $this->assertIsType(OrmProduct::class, $orm);
        /* @var Product $domain */
        /* @var OrmProduct $orm */

        $orm->sku = $domain->getSku()->value();
        $orm->status = $domain->getStatus()->value();
        $orm->updatedBy = $domain->getUpdatedBy()?->value();

        $this->mapPricesFromDomainToOrm($domain, $orm);
        $this->mapCategoriesFromDomainToOrm($domain, $orm);
        $this->mapTranslationsFromDomainToOrm($domain, $orm);
        $this->mapAttributesFromDomainToOrm($domain, $orm);
        $this->mapImagesFromDomainToOrm($domain, $orm);
    }

    /**
     * @return ProductPrice[]
     *
     * @throws InvalidCatalogValueObjectException
     * @throws EntityIdMissingException
     */
    private function mapPricesFromOrmToDomain(OrmProduct $orm): array
    {
        $prices = [];
        foreach ($orm->prices as $ormPrice) {
            $id = ProductPriceId::fromInt($ormPrice->id ?? throw EntityIdMissingException::forEntity($ormPrice::class));

            $prices[] = new ProductPrice(
                price: new Price(
                    amount: $ormPrice->amount,
                    currency: $ormPrice->currency,
                ),
                type: Type::fromEnum($ormPrice->type),
                tax: new Tax(value: (float) $ormPrice->taxValue, type: $ormPrice->taxType),
                taxIncluded: TaxIncludedFlag::fromBool($ormPrice->taxIncluded),
                validFrom: $ormPrice->validFrom ? ValidFrom::fromDateTime($ormPrice->validFrom) : null,
                validTo: $ormPrice->validTo ? ValidTo::fromDateTime($ormPrice->validTo) : null,
                id: $id,
            );
        }

        return $prices;
    }

    private function mapPricesFromDomainToOrm(Product $domain, OrmProduct $orm): void
    {
        $domainPrices = $domain->getPrices();
        $currentOrmPrices = $orm->prices->toArray();

        foreach ($currentOrmPrices as $ormPrice) {
            $stillExists = array_any($domainPrices, fn (ProductPrice $dp) => $dp->getId()?->value() === $ormPrice->id);
            if (!$stillExists) {
                $orm->prices->removeElement($ormPrice);
            }
        }

        foreach ($domainPrices as $dp) {
            $ormProductPrice = array_find($currentOrmPrices, fn (OrmProductPrice $p) => $p->id === $dp->getId()?->value()
            ) ?? new OrmProductPrice();

            if (null === $ormProductPrice->id) {
                $ormProductPrice->product = $orm;
                $orm->prices->add($ormProductPrice);
            }

            $this->mapPriceItemFromDomainToOrm($dp, $ormProductPrice);
        }
    }

    private function mapPriceItemFromDomainToOrm(ProductPrice $productPrice, OrmProductPrice $ormProductPrice): void
    {
        $ormProductPrice->amount = $productPrice->getPrice()->getAmount();
        $ormProductPrice->currency = $productPrice->getPrice()->getCurrency();
        $ormProductPrice->type = $productPrice->getType()->value();
        $ormProductPrice->taxValue = (string) $productPrice->getTax()->getValue();
        $ormProductPrice->taxType = $productPrice->getTax()->getType();
        $ormProductPrice->taxIncluded = $productPrice->getTaxIncluded()->value();
        $ormProductPrice->validFrom = $productPrice->getValidFrom()?->value();
        $ormProductPrice->validTo = $productPrice->getValidTo()?->value();
    }

    /**
     * @return CategoryId[]
     *
     * @throws InvalidCategoryIdException
     */
    private function mapCategoriesFromOrmToDomain(OrmProduct $orm): array
    {
        $categoryIds = [];
        foreach ($orm->categories as $ormCategory) {
            $categoryIds[] = CategoryId::fromInt($ormCategory->id);
        }

        return $categoryIds;
    }

    private function mapCategoriesFromDomainToOrm(Product $domain, OrmProduct $orm): void
    {
        $domainCategoryIds = array_map(fn ($id) => $id->value(), $domain->getCategoryIds());

        $check = array_combine($domainCategoryIds, $domainCategoryIds);
        foreach ($orm->categories as $ormCategory) {
            if (!isset($check[$ormCategory->id])) {
                $orm->categories->removeElement($ormCategory);
            }
        }

        foreach ($domainCategoryIds as $id) {
            $exists = $orm->categories->exists(fn (mixed $key, OrmCategory $c) => $c->id === $id);
            if (!$exists) {
                $orm->categories->add($this->referenceProvider->getReference(className: OrmCategory::class, id: $id));
            }
        }
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function mapTranslationsFromOrmToDomain(OrmProduct $orm): Translations
    {
        $translations = [];
        foreach ($orm->translations as $ormTranslation) {
            $translations[$ormTranslation->locale] = [
                'name' => $ormTranslation->name,
                'description' => $ormTranslation->description,
            ];
        }

        return Translations::fromArray($translations);
    }

    private function mapTranslationsFromDomainToOrm(Product $domain, OrmProduct $orm): void
    {
        $domainTranslations = $domain->getTranslations();

        foreach ($orm->translations as $ormTranslation) {
            if (null === $domainTranslations->get($ormTranslation->locale)) {
                $orm->translations->removeElement($ormTranslation);
            }
        }

        foreach ($domainTranslations as $locale => $translation) {
            $existing = $orm->translations->filter(fn (OrmProductTranslation $t) => $t->locale === $locale)->first();

            if ($existing) {
                $existing->name = $translation->name;
                $existing->description = $translation->description;
            } else {
                $ormTranslation = new OrmProductTranslation();
                $ormTranslation->product = $orm;
                $ormTranslation->locale = $locale;
                $ormTranslation->name = $translation->name;
                $ormTranslation->description = $translation->description;

                $orm->translations->add($ormTranslation);
            }
        }
    }

    /**
     * @return ProductAttributeValue[]
     *
     * @throws InvalidCatalogValueObjectException
     * @throws EntityIdMissingException
     */
    private function mapAttributesFromOrmToDomain(OrmProduct $orm): array
    {
        $attributeValues = [];
        foreach ($orm->attributeValues as $ormValue) {
            $id = ProductAttributeValueId::fromInt($ormValue->id ?? throw EntityIdMissingException::forEntity($ormValue::class));

            $value = match ($ormValue->attribute->type) {
                TypeEnum::String => StringValue::fromString((string) $ormValue->valueString),
                TypeEnum::Int => IntegerValue::fromInt((int) $ormValue->valueInt),
                TypeEnum::Boolean => BooleanValue::fromBool((bool) $ormValue->valueBoolean),
                TypeEnum::Select => ArrayValue::fromArray((array) $ormValue->valueJson),
                null => throw new InvalidArgumentException(sprintf('%s with id %d has null type', OrmProductAttributeValue::class, (int) $ormValue->id)),
            };

            $attributeValues[] = new ProductAttributeValue(
                id: $id,
                attributeId: AttributeId::fromInt($ormValue->attribute->id),
                value: $value
            );
        }

        return $attributeValues;
    }

    private function mapAttributesFromDomainToOrm(Product $domain, OrmProduct $orm): void
    {
        $domainValues = $domain->getAttributeValues();
        $currentOrmValues = $orm->attributeValues->toArray();

        foreach ($currentOrmValues as $ormValue) {
            $stillExists = array_any(
                $domainValues,
                fn (ProductAttributeValue $pav) => $pav->getId()?->value() === $ormValue->id
            );
            if (!$stillExists) {
                $orm->attributeValues->removeElement($ormValue);
            }
        }

        foreach ($domainValues as $dv) {
            $ormValue = array_find(
                $currentOrmValues,
                fn (OrmProductAttributeValue $p) => $p->id === $dv->getId()?->value()
            ) ?? new OrmProductAttributeValue();

            if (null === $ormValue->id) {
                $ormValue->product = $orm;
                $ormValue->attribute = $this->referenceProvider->getReference(
                    className: OrmAttribute::class,
                    id: $dv->getAttributeId()->value()
                );

                $orm->attributeValues->add($ormValue);
            }

            $this->mapAttributeValueFromDomainToOrm($dv, $ormValue);
        }
    }

    private function mapAttributeValueFromDomainToOrm(
        ProductAttributeValue $domain,
        OrmProductAttributeValue $orm,
    ): void {
        $vo = $domain->getValue();

        $orm->valueString = null;
        $orm->valueInt = null;
        $orm->valueBoolean = null;
        $orm->valueJson = null;

        match (true) {
            $vo instanceof StringValue => $orm->valueString = $vo->value(),
            $vo instanceof IntegerValue => $orm->valueInt = $vo->value(),
            $vo instanceof BooleanValue => $orm->valueBoolean = $vo->value(),
            $vo instanceof ArrayValue => $orm->valueJson = $vo->value(),
            default => throw new InvalidArgumentException('Unknown attribute value type'),
        };
    }

    /**
     * @return ProductImage[]
     *
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidRelativePathException
     * @throws EntityIdMissingException
     */
    private function mapImagesFromOrmToDomain(OrmProduct $orm): array
    {
        $images = [];
        foreach ($orm->images as $ormImage) {
            $id = ProductImageId::fromInt($ormImage->id ?? throw EntityIdMissingException::forEntity($ormImage::class));

            $images[] = new ProductImage(
                ulid: ProductImageUlid::fromString($ormImage->ulid),
                path: RelativeFilePath::fromString($ormImage->path),
                sortOrder: SortOrder::fromInt($ormImage->sortOrder),
                isMain: MainImageFlag::fromBool($ormImage->isMain),
                id: $id,
            );
        }

        return $images;
    }

    private function mapImagesFromDomainToOrm(Product $domain, OrmProduct $orm): void
    {
        $domainImages = $domain->getImages();
        $currentOrmImages = $orm->images->toArray();

        foreach ($currentOrmImages as $ormImage) {
            $stillExists = array_any($domainImages, fn (ProductImage $pi) => $pi->getId()?->value() === $ormImage->id);
            if (!$stillExists) {
                $orm->images->removeElement($ormImage);
            }
        }

        foreach ($domainImages as $di) {
            $ormImage = array_find(
                $currentOrmImages,
                fn (OrmProductImage $p) => $p->id === $di->getId()?->value()
            ) ?? new OrmProductImage();

            if (null === $ormImage->id) {
                $ormImage->product = $orm;
                $ormImage->ulid = $di->getUlid()->value();

                $orm->images->add($ormImage);
            }

            $this->mapImageItemFromDomainToOrm($di, $ormImage);
        }
    }

    private function mapImageItemFromDomainToOrm(ProductImage $productImage, OrmProductImage $ormProductImage): void
    {
        $ormProductImage->path = $productImage->getPath()->value();
        $ormProductImage->sortOrder = $productImage->getSortOrder()->value();
        $ormProductImage->isMain = $productImage->isMain()->value();
    }
}
