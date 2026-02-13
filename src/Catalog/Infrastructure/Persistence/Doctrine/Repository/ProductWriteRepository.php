<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Repository\ProductWriteRepositoryInterface;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProduct;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;

final class ProductWriteRepository extends BaseProductRepository implements ProductWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     * @throws ValueObjectExceptionInterface
     */
    public function save(Product $product): Product
    {
        $orm = $this->_save(domain: $product, id: $product->getId()?->value());

        return $this->mapper->fromDoctrineOrm($orm);
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function delete(Product $product): void
    {
        $this->_delete($product);
    }

    protected function findOrmForUpdateFallback(string $stringId): ?OrmProduct
    {
        $orm = $this->getEntityManager()->createQueryBuilder()
            ->select('p', 't', 'c', 'av', 'a')
            ->from(OrmProduct::class, 'p')
            ->leftJoin('p.translations', 't')
            ->leftJoin('p.categories', 'c')
            ->leftJoin('p.attributeValues', 'av')
            ->leftJoin('av.attribute', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $stringId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($orm instanceof OrmProduct) {
            return $orm;
        }

        return null;
    }
}
