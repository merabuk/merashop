<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Exception\Product\ProductNotFoundException;
use App\Catalog\Domain\Repository\ProductReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProduct;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\ReadRepositoryTrait;

final class ProductReadRepository extends BaseProductRepository implements ProductReadRepositoryInterface
{
    use ReadRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws ProductNotFoundException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function getById(Id $id): Product
    {
        return $this->findById($id) ?? throw new ProductNotFoundException();
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function findById(Id $id): ?Product
    {
        $orm = $this->find($id->value());

        return $this->checkAndMapToDomain($orm);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function findByUlid(Ulid $ulid): ?Product
    {
        $orm = $this->findOneBy(['ulid' => $ulid->value()]);

        return $this->checkAndMapToDomain($orm);
    }

    public function existsBySku(Sku $sku): bool
    {
        return $this->_existsBy([
            $this->_makeCriterion(field: 'sku', value: $sku->value()),
        ]);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function checkAndMapToDomain(?object $orm): ?Product
    {
        if (false === $orm instanceof OrmProduct) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($orm);
    }
}
