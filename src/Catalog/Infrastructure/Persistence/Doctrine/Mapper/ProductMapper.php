<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Exception\Category\InvalidCategoryIdException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Exception\Product\InvalidProductCategoryIdItemException;
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
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
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
     * @throws ProductPriceStateException
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
            ulid: ProductUlid::fromString($orm->ulid),
            sku: Sku::fromString($orm->sku),
            status: Status::fromEnum($orm->status),
            translations: $translations,
            version: Version::fromInt($orm->version),
            createdBy: AdminUlid::fromString($orm->createdBy),
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
     * @throws EntityIdMissingException
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
        $currentOrmPrices = $orm->prices->toArray();

        foreach ($currentOrmPrices as $ormPrice) {
            $stillExists = $domainPrices->getCurrencyAndType($ormPrice->currency, $ormPrice->type);

            if (!$stillExists) {
                $orm->prices->removeElement($ormPrice);
            }
        }

        foreach ($domainPrices as $dp) {
            $ormProductPrice = array_find($currentOrmPrices, fn (OrmProductPrice $p) => $p->type === $dp->getType()->value()
                && $p->currency === $dp->getPrice()->getCurrency()
            );

            if (!$ormProductPrice) {
                $ormProductPrice = new OrmProductPrice();
                $ormProductPrice->product = $orm;
                $orm->prices->add($ormProductPrice);
            }

            $this->productPriceMapper->mapToExistingOrm($dp, $ormProductPrice);
        }
    }

    /**
     * @throws InvalidCategoryIdException
     * @throws InvalidProductCategoryIdItemException
     */
    private function mapCategoriesFromOrmToDomain(OrmProduct $orm): CategoryIdCollection
    {
        $categoryIds = [];
        foreach ($orm->categories as $ormCategory) {
            $categoryIds[] = CategoryId::fromInt($ormCategory->id);
        }

        return CategoryIdCollection::fromArray($categoryIds);
    }

    private function mapCategoriesFromDomainToOrm(Product $domain, OrmProduct $orm): void
    {
        $domainCategoryIds = array_map(fn (CategoryId $id) => $id->value(), $domain->getCategoryIds()->all());

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
     * @throws InvalidCatalogValueObjectException
     * @throws EntityIdMissingException
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
        $currentOrmValues = $orm->attributeValues->toArray();

        foreach ($currentOrmValues as $ormValue) {
            $stillExists = $domainValues->getByAttributeId($ormValue->attribute->id);
            if (!$stillExists) {
                $orm->attributeValues->removeElement($ormValue);
            }
        }

        foreach ($domainValues as $dv) {
            $ormValue = array_find(
                $currentOrmValues,
                fn (OrmProductAttributeValue $p) => $p->attribute->id === $dv->getAttributeId()->value()
            );

            if (!$ormValue) {
                $ormValue = new OrmProductAttributeValue();
                $ormValue->product = $orm;
                $ormValue->attribute = $this->referenceProvider->getReference(
                    className: OrmAttribute::class,
                    id: $dv->getAttributeId()->value()
                );

                $orm->attributeValues->add($ormValue);
            }

            $this->productAttributeValueMapper->mapToExistingOrm($dv, $ormValue);
        }
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidRelativePathException
     * @throws EntityIdMissingException
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
        $currentOrmImages = $orm->images->toArray();

        foreach ($currentOrmImages as $ormImage) {
            $stillExists = $domainImages->getByUlid($ormImage->ulid);
            if (!$stillExists) {
                $orm->images->removeElement($ormImage);
            }
        }

        foreach ($domainImages as $di) {
            $ormImage = array_find(
                $currentOrmImages,
                fn (OrmProductImage $p) => $p->ulid === $di->getUlid()->value()
            ) ?? new OrmProductImage();

            if (null === $ormImage->id) {
                $ormImage->product = $orm;
                $ormImage->ulid = $di->getUlid()->value();

                $orm->images->add($ormImage);
            }

            $this->productImageMapper->mapToExistingOrm($di, $ormImage);
        }
    }
}
