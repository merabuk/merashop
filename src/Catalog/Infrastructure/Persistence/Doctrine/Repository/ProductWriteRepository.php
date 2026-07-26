<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\Exception\ProductPrice\ProductPriceStateException;
use App\Catalog\Domain\Repository\ProductWriteRepositoryInterface;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProduct;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class ProductWriteRepository extends BaseProductRepository implements ProductWriteRepositoryInterface
{
    /**
     * @use WriteRepositoryTrait<Product, OrmProduct>
     */
    use WriteRepositoryTrait;

    /**
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws InvalidRelativePathException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ProductAttributeValueStateException
     * @throws ProductPriceStateException
     */
    public function save(Product $product): Product
    {
        $orm = $this->_save(domain: $product, id: $product->getId()?->value());

        return $this->mapper->fromDoctrineOrm($orm);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(Product $product): void
    {
        $this->_delete($product->getId()?->value());
    }

    protected function findOrmForUpdateFallback(string $stringId): ?OrmProduct
    {
        $orm = $this->getEntityManager()->createQueryBuilder()
            ->select('p', 'pt', 'c', 'pav', 'a', 'o', 'pp', 'pi')
            ->from(OrmProduct::class, 'p')
            ->leftJoin('p.translations', 'pt')
            ->leftJoin('p.categories', 'c')
            ->leftJoin('p.attributeValues', 'pav')
            ->leftJoin('pav.attribute', 'a')
            ->leftJoin('pav.option', 'o')
            ->leftJoin('p.prices', 'pp')
            ->leftJoin('p.images', 'pi')
            ->where('p.id = :id')
            ->setParameter('id', $stringId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($orm instanceof OrmProduct) {
            return $orm;
        }

        return null;
    }
}
