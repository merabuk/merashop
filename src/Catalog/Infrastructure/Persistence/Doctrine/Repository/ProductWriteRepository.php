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
        $em = $this->getEntityManager();
        $id = (string) $product->getId()?->value();

        if (!$id) {
            $ormProduct = $em->getUnitOfWork()->tryGetById($id, self::getEntityClass()) ?: null;
        } else {
            $ormProduct = $this->mapper->fromDoctrineOrm($product);
        }

        $ormProduct ??= $this->findOrmForUpdateFallback($id);

        if (!$ormProduct) {
            throw $this->makeRuntimeException($id);
        }

        $this->mapper->mapToExistingOrm($product, $ormProduct);

        if (!$id) {
            $em->persist($ormProduct);
        }

        $em->flush();

        return $this->mapper->fromDoctrineOrm($ormProduct);
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
            ->from(self::getEntityClass(), 'p')
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
