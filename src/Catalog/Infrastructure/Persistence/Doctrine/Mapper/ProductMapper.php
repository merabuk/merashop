<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Price;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Catalog\Domain\ValueObject\ProductAttribute\ArrayValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\BooleanValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\IntegerValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\StringValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategory;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProduct;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductAttributeValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductTranslation;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;
use App\Catalog\Domain\ValueObject\ProductAttribute\Id as ProductAttributeValueId;
use InvalidArgumentException;

class ProductMapper
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmProduct
    {
        $orm = new OrmProduct();
        $this->mapToExistingOrm($domain, $orm);

        return $orm;
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidCatalogValueObjectException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidLocaleException
     */
    public function fromDoctrineOrm(object $orm): Product
    {
        $this->assertIsType(OrmProduct::class, $orm);
        /** @var OrmProduct $orm */

        $translations = [];
        foreach ($orm->translations as $ormTranslation) {
            $translations[$ormTranslation->locale] = [
                'name' => $ormTranslation->name,
                'description' => $ormTranslation->description,
            ];
        }

        $categoryIds = [];
        foreach ($orm->categories as $ormCategory) {
            $categoryIds[] = CategoryId::fromInt($ormCategory->id);
        }

        $productId = Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class));

        $attributeValues = [];
        foreach ($orm->attributeValues as $ormValue) {
            $value = match ($ormValue->attribute->type) {
                TypeEnum::String, TypeEnum::Select => StringValue::fromString($ormValue->valueString),
                TypeEnum::Int => IntegerValue::fromInt($ormValue->valueInt),
                TypeEnum::Boolean => BooleanValue::fromBool($ormValue->valueBoolean),
            };

            $attributeValues[] = new ProductAttributeValue(
                id: ProductAttributeValueId::fromInt($ormValue->id),
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
            translations: Translations::fromArray($translations),
            categoryIds: $categoryIds,
            attributeValues: $attributeValues
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

        $orm->ulid = $domain->getUlid()->value();
        $orm->sku = $domain->getSku()->value();
        $orm->priceAmount = $domain->getPrice()->getAmount();
        $orm->priceCurrency = $domain->getPrice()->getCurrency()->value;
        $orm->status = $domain->getStatus()->value();

        $this->mapCategories($domain, $orm);
        $this->mapTranslations($domain, $orm);
        $this->mapAttributes($domain, $orm);
    }

    private function mapCategories(Product $domain, OrmProduct $orm): void
    {
        $domainIds = array_map(fn($id) => $id->value(), $domain->getCategoryIds());

        foreach ($orm->categories as $ormCategory) {
            if (!in_array($ormCategory->id, $domainIds, true)) {
                $orm->categories->removeElement($ormCategory);
            }
        }

        foreach ($domainIds as $id) {
            $exists = $orm->categories->exists(fn(mixed $key, OrmCategory $c) => $c->id === $id);
            if (!$exists) {
                // TODO: avoid use entity reference here
                $orm->categories->add($this->entityManager->getReference(OrmCategory::class, $id));
            }
        }
    }

    private function mapTranslations(Product $domain, OrmProduct $orm): void
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

    private function mapAttributes(Product $domain, OrmProduct $orm): void
    {
        $domainValues = $domain->getAttributeValues();

        foreach ($orm->attributeValues as $ormValue) {
            $found = false;
            foreach ($domainValues as $dv) {
                if ($dv->getAttributeId()->value() === $ormValue->attribute->id) {
                    $found = true; break;
                }
            }
            if (!$found) $orm->attributeValues->removeElement($ormValue);
        }

        foreach ($domainValues as $dv) {
            $ormValue = $orm->attributeValues->filter(
                fn(OrmProductAttributeValue $o) => $o->attribute->id === $dv->getAttributeId()->value()
            )->first() ?: null;

            if (!$ormValue) {
                $ormValue = new OrmProductAttributeValue();
                $ormValue->product = $orm;
                // TODO: avoid use entity reference here
                $ormValue->attribute = $this->entityManager->getReference(OrmAttribute::class, $dv->getAttributeId()->value());

                $orm->attributeValues->add($ormValue);
            }

            $this->mapAttributeValue($dv, $ormValue);
        }
    }

    private function mapAttributeValue(ProductAttributeValue $domain, OrmProductAttributeValue $orm): void
    {
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
            default => throw new InvalidArgumentException("Unknown attribute value type")
        };
    }
}
