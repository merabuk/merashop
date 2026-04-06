<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Exception\Product\ProductNotFoundException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\Exception\ProductPrice\ProductPriceStateException;
use App\Catalog\Domain\Repository\ProductReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProduct;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\ReadRepositoryTrait;
use Doctrine\ORM\QueryBuilder;

final class ProductReadRepository extends BaseProductRepository implements ProductReadRepositoryInterface
{
    use ReadRepositoryTrait;

    private const string ALIAS = 'p';
    private const string ALIAS_TRANSLATIONS = 't';
    private const string ALIAS_CATEGORIES = 'c';
    private const string ALIAS_PRICES = 'pp';
    private const string ALIAS_ATTRIBUTE_VALUES = 'pav';
    private const string ALIAS_ATTRIBUTE = 'a';
    private const string ALIAS_OPTION = 'o';
    private const string ALIAS_IMAGES = 'pi';

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws InvalidRelativePathException
     * @throws ProductAttributeValueStateException
     * @throws ProductNotFoundException
     * @throws ProductPriceStateException
     */
    public function getById(Id $id): Product
    {
        return $this->findById($id) ?? throw ProductNotFoundException::withId($id->value());
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws InvalidRelativePathException
     * @throws ProductAttributeValueStateException
     * @throws ProductPriceStateException
     */
    public function findById(Id $id): ?Product
    {
        $qb = $this->createBaseQueryBuilder();

        $this->joinRelations($qb);

        $orm = $this->_findById(id: $id, alias: self::ALIAS, qb: $qb);

        return $this->checkAndMapToDomain($orm);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws InvalidRelativePathException
     * @throws ProductAttributeValueStateException
     * @throws ProductPriceStateException
     */
    public function findByUlid(Ulid $ulid): ?Product
    {
        $qb = $this->createBaseQueryBuilder();

        $this->joinRelations($qb);

        $orm = $this->_findByUlid(ulid: $ulid, alias: self::ALIAS, qb: $qb);

        return $this->checkAndMapToDomain($orm);
    }

    public function existsBySku(Sku $sku): bool
    {
        return $this->_existsBy(criteria: [
            $this->_makeCriterion(field: 'sku', value: $sku->value()),
        ], alias: self::ALIAS);
    }

    private function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }

    private function joinRelations(QueryBuilder $qb): void
    {
        $this->joinTranslations($qb);
        $this->joinCategories($qb);
        $this->joinPrices($qb);
        $this->joinAttributeValues($qb);
        $this->joinImages($qb);
    }

    private function joinTranslations(QueryBuilder $qb): void
    {
        $qb->leftJoin(self::ALIAS.'.translations', self::ALIAS_TRANSLATIONS)
            ->addSelect(self::ALIAS_TRANSLATIONS);
    }

    private function joinCategories(QueryBuilder $qb): void
    {
        $qb->leftJoin(self::ALIAS.'.categories', self::ALIAS_CATEGORIES)
            ->addSelect(self::ALIAS_CATEGORIES);
    }

    private function joinPrices(QueryBuilder $qb): void
    {
        $qb->leftJoin(self::ALIAS.'.prices', self::ALIAS_PRICES)
            ->addSelect(self::ALIAS_PRICES);
    }

    private function joinAttributeValues(QueryBuilder $qb): void
    {
        $qb->leftJoin(self::ALIAS.'.attributeValues', self::ALIAS_ATTRIBUTE_VALUES)
            ->addSelect(self::ALIAS_ATTRIBUTE_VALUES)
            ->leftJoin(self::ALIAS_ATTRIBUTE_VALUES.'.attribute', self::ALIAS_ATTRIBUTE)
            ->addSelect(self::ALIAS_ATTRIBUTE)
            ->leftJoin(self::ALIAS_ATTRIBUTE_VALUES.'.option', self::ALIAS_OPTION);
    }

    private function joinImages(QueryBuilder $qb): void
    {
        $qb->leftJoin(self::ALIAS.'.images', self::ALIAS_IMAGES)
            ->addSelect(self::ALIAS_IMAGES);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws InvalidRelativePathException
     * @throws ProductAttributeValueStateException
     * @throws ProductPriceStateException
     */
    private function checkAndMapToDomain(?object $orm): ?Product
    {
        if (false === $orm instanceof OrmProduct) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($orm);
    }
}
