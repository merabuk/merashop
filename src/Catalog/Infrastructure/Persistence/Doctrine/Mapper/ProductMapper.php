<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\Exception\Category\InvalidCategoryIdException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Exception\Product\InvalidProductCategoryIdItemException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\Exception\ProductPrice\ProductPriceStateException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\AttributeValueCollection;
use App\Catalog\Domain\ValueObject\Product\CategoryIdCollection;
use App\Catalog\Domain\ValueObject\Product\Id as ProductId;
use App\Catalog\Domain\ValueObject\Product\ImageCollection;
use App\Catalog\Domain\ValueObject\Product\PriceCollection;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid as ProductUlid;
use App\Catalog\Domain\ValueObject\Product\Version;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategory;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProduct;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductAttributeValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductImage;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductPrice;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductTranslation;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Infrastructure\Persistence\Doctrine\Interface\ProxyReferenceProviderInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<Product, OrmProduct>
 */
final readonly class ProductMapper implements MapperInterface
{
    use TypeCheckTrait;

    public function __construct(
        private ProxyReferenceProviderInterface $referenceProvider,
        private ProductPriceMapper $productPriceMapper,
        private ProductImageMapper $productImageMapper,
        private ProductAttributeValueMapper $productAttributeValueMapper,
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
        $orm->createdBy = $domain->getCreatedBy()->value();

        $this->mapToExistingOrm($domain, $orm);

        return $orm;
    }

    /**
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws InvalidRelativePathException
     * @throws ProductAttributeValueStateException
     * @throws ProductPriceStateException
     */
    public function fromDoctrineOrm(object $orm): Product
    {
        $this->assertIsType(OrmProduct::class, $orm);
        /** @var OrmProduct $orm */
        $productId = ProductId::fromInt($orm->id ?? throw EntityFieldMissingException::forEntityId($orm::class));

        $prices = $this->mapPricesFromOrmToDomain($orm);
        $categoryIds = $this->mapCategoriesFromOrmToDomain($orm);
        $translations = $this->mapTranslationsFromOrmToDomain($orm);
        $attributeValues = $this->mapAttributesFromOrmToDomain($orm);
        $images = $this->mapImagesFromOrmToDomain($orm);

        return new Product(
            ulid: ProductUlid::fromString($orm->ulid ?? throw EntityFieldMissingException::forField(field: 'ulid', className: $orm::class)),
            sku: Sku::fromString($orm->sku ?? throw EntityFieldMissingException::forField(field: 'sku', className: $orm::class)),
            status: Status::fromEnum($orm->status),
            translations: $translations,
            version: Version::fromInt($orm->version ?? throw EntityFieldMissingException::forField(field: 'version', className: $orm::class)),
            createdBy: AdminUlid::fromString($orm->createdBy ?? throw EntityFieldMissingException::forField(field: 'createdBy', className: $orm::class)),
            prices: $prices,
            categoryIds: $categoryIds,
            attributeValues: $attributeValues,
            images: $images,
            updatedBy: $orm->updatedBy ? AdminUlid::fromString($orm->updatedBy) : null,
            id: $productId,
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
        $this->mapAttributeValuesFromDomainToOrm($domain, $orm);
        $this->mapImagesFromDomainToOrm($domain, $orm);
    }

    /**
     * @throws EntityFieldMissingException
     * @throws InvalidCatalogValueObjectException
     * @throws ProductPriceStateException
     */
    private function mapPricesFromOrmToDomain(OrmProduct $orm): PriceCollection
    {
        $prices = [];
        foreach ($orm->prices as $ormPrice) {
            $prices[] = $this->productPriceMapper->toDomain($ormPrice);
        }

        return PriceCollection::fromArray($prices);
    }

    private function mapPricesFromDomainToOrm(Product $domain, OrmProduct $orm): void
    {
        $domainPrices = $domain->getPrices();

        $existingOrmPrices = [];
        foreach ($orm->prices as $ormPrice) {
            $existingOrmPrices[$this->getPriceKey(
                currency: $ormPrice->currency,
                type: $ormPrice->type,
            )] = $ormPrice;
        }

        foreach ($existingOrmPrices as $ormPrice) {
            $stillExists = $domainPrices->getByCurrencyAndType($ormPrice->currency, $ormPrice->type);
            if (!$stillExists) {
                $orm->prices->removeElement($ormPrice);
            }
        }

        foreach ($domainPrices as $dp) {
            $ormProductPrice = $existingOrmPrices[$this->getPriceKey(
                currency: $dp->getPrice()->getCurrency(),
                type: $dp->getType()->value(),
            )] ?? null;

            if (!$ormProductPrice) {
                $ormProductPrice = new OrmProductPrice();
                $ormProductPrice->product = $orm;
                $ormProductPrice->type = $dp->getType()->value();
                $ormProductPrice->currency = $dp->getPrice()->getCurrency();
                $ormProductPrice->createdBy = $dp->getCreatedBy()->value();

                $orm->prices->add($ormProductPrice);
            }

            $this->productPriceMapper->mapToExistingOrm($dp, $ormProductPrice);
        }
    }

    private function getPriceKey(CurrencyEnum $currency, TypeEnum $type): string
    {
        return sprintf('%s_%s', $currency->value, $type->value);
    }

    /**
     * @throws EntityFieldMissingException
     * @throws InvalidCategoryIdException
     * @throws InvalidProductCategoryIdItemException
     */
    private function mapCategoriesFromOrmToDomain(OrmProduct $orm): CategoryIdCollection
    {
        $categoryIds = [];
        foreach ($orm->categories as $ormCategory) {
            $categoryIds[] = CategoryId::fromInt($ormCategory->id ?? throw EntityFieldMissingException::forEntityId(className: $ormCategory::class));
        }

        return CategoryIdCollection::fromArray($categoryIds);
    }

    private function mapCategoriesFromDomainToOrm(Product $domain, OrmProduct $orm): void
    {
        $domainCategoryIds = array_map(fn (CategoryId $id) => $id->value(), $domain->getCategoryIds()->all());

        $check = [] !== $domainCategoryIds ? array_combine($domainCategoryIds, $domainCategoryIds) : [];

        $existingOrmCategories = [];
        foreach ($orm->categories as $ormCategory) {
            $existingOrmCategories[$ormCategory->id] = $ormCategory;
        }

        foreach ($existingOrmCategories as $id => $ormCategory) {
            if (!isset($check[$id])) {
                $orm->categories->removeElement($ormCategory);
            }
        }

        foreach ($domainCategoryIds as $id) {
            $exists = $existingOrmCategories[$id] ?? null;
            if (!$exists) {
                $orm->categories->add($this->referenceProvider->getReference(className: OrmCategory::class, id: $id));
            }
        }
    }

    /**
     * @throws EntityFieldMissingException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function mapTranslationsFromOrmToDomain(OrmProduct $orm): Translations
    {
        $translations = [];
        foreach ($orm->translations as $ormTranslation) {
            $translations[$ormTranslation->locale] = [
                'name' => $ormTranslation->name ?? throw EntityFieldMissingException::forField(field: 'name', className: $ormTranslation::class),
                'description' => $ormTranslation->description,
            ];
        }

        return Translations::fromArray($translations);
    }

    private function mapTranslationsFromDomainToOrm(Product $domain, OrmProduct $orm): void
    {
        $domainTranslations = $domain->getTranslations();

        $existingOrmTranslations = [];
        foreach ($orm->translations as $t) {
            $existingOrmTranslations[$t->locale] = $t;
        }

        foreach ($existingOrmTranslations as $locale => $ormTranslation) {
            if (!$domainTranslations->has($locale)) {
                $orm->translations->removeElement($ormTranslation);
            }
        }

        foreach ($domainTranslations as $locale => $translation) {
            $ormTranslation = $existingOrmTranslations[$locale] ?? null;

            if (!$ormTranslation) {
                $ormTranslation = new OrmProductTranslation();
                $ormTranslation->product = $orm;
                $ormTranslation->locale = $locale;

                $orm->translations->add($ormTranslation);
            }

            $ormTranslation->name = $translation->name;
            $ormTranslation->description = $translation->description;
        }
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws EntityFieldMissingException
     * @throws ProductAttributeValueStateException
     */
    private function mapAttributesFromOrmToDomain(OrmProduct $orm): AttributeValueCollection
    {
        $attributeValues = [];
        foreach ($orm->attributeValues as $ormValue) {
            $attributeValues[] = $this->productAttributeValueMapper->toDomain($ormValue);
        }

        return AttributeValueCollection::fromArray($attributeValues);
    }

    private function mapAttributeValuesFromDomainToOrm(Product $domain, OrmProduct $orm): void
    {
        $domainValues = $domain->getAttributeValues();

        $existingOrmValues = [];
        foreach ($orm->attributeValues as $ormValue) {
            if (null === $ormValue->attribute) {
                // TODO: add handler for this case

                continue;
            }
            $existingOrmValues[(int) $ormValue->attribute->id] = $ormValue;
        }

        foreach ($existingOrmValues as $attributeId => $ormValue) {
            if (!$domainValues->getByAttributeId((int) $attributeId)) {
                $orm->attributeValues->removeElement($ormValue);
            }
        }

        foreach ($domainValues as $dv) {
            $ormValue = $existingOrmValues[$dv->getAttributeId()->value()] ?? null;

            if (!$ormValue) {
                $ormValue = new OrmProductAttributeValue();
                $ormValue->product = $orm;
                $ormValue->attribute = $this->referenceProvider->getReference(
                    className: OrmAttribute::class,
                    id: $dv->getAttributeId()->value()
                );
                $ormValue->createdBy = $dv->getCreatedBy()->value();

                $orm->attributeValues->add($ormValue);
            }

            $this->productAttributeValueMapper->mapToExistingOrm($dv, $ormValue);
        }
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidRelativePathException
     * @throws EntityFieldMissingException
     */
    private function mapImagesFromOrmToDomain(OrmProduct $orm): ImageCollection
    {
        $images = [];
        foreach ($orm->images as $ormImage) {
            $images[] = $this->productImageMapper->toDomain($ormImage);
        }

        return ImageCollection::fromArray($images);
    }

    private function mapImagesFromDomainToOrm(Product $domain, OrmProduct $orm): void
    {
        $domainImages = $domain->getImages();

        $existingOrmImages = [];
        foreach ($orm->images as $ormImage) {
            $ormImage->isMain = false;
            $existingOrmImages[$ormImage->ulid] = $ormImage;
        }

        foreach ($existingOrmImages as $ulid => $ormImage) {
            if (!$domainImages->getByUlid($ulid)) {
                $orm->images->removeElement($ormImage);
            }
        }

        foreach ($domainImages as $di) {
            $ormImage = $existingOrmImages[$di->getUlid()->value()] ?? null;
            if (!$ormImage) {
                $ormImage = new OrmProductImage();
                $ormImage->product = $orm;
                $ormImage->ulid = $di->getUlid()->value();

                $orm->images->add($ormImage);
            }

            $this->productImageMapper->mapToExistingOrm($di, $ormImage);
        }
    }
}
