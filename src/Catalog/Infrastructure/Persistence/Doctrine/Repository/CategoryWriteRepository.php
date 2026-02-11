<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Domain\Service\CategoryPathGenerator;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;

final class CategoryWriteRepository extends BaseCategoryRepository implements CategoryWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     * @throws ValueObjectExceptionInterface
     */
    public function save(Category $category): Category
    {
        $em = $this->getEntityManager();

        $parentId = (string) $category->getParentId()?->value();
        $ormParent = null;
        if ($parentId) {
            $ormParent = $em->getUnitOfWork()->tryGetById($parentId, self::getEntityClass()) ?: null;
            $ormParent ??= $em->find(self::getEntityClass(), $parentId);
        }

        $id = (string) $category->getId()?->value();

        if ($id) {
            $ormCategory = $em->getUnitOfWork()->tryGetById($id, self::getEntityClass()) ?: null;
        } else {
            $ormCategory = $this->mapper->toDoctrineOrm($category, $ormParent);
        }

        $ormCategory ??= $this->findOrmForUpdateFallback($id);

        if (!$ormCategory) {
            throw $this->makeRuntimeException($id);
        }

        $this->mapper->mapToExistingOrm($category, $ormCategory, $ormParent);

        if (!$id) {
            $em->persist($ormCategory);
        }

        $em->flush();

        return $this->mapper->fromDoctrineOrm($ormCategory);
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function delete(Category $category): void
    {
        $this->_delete($category);
    }

    public function replaceOldPathOnNew(Path $oldPath, Path $newPath): void
    {
        $this->createQueryBuilder('cw')
            ->update()
            ->set('cw.path', 'CONCAT(:newPath, SUBSTRING(cw.path, :oldPathLength))')
            ->where('cw.path LIKE :oldPathPrefix')
            ->setParameter('newPath', $newPath->value())
            ->setParameter('oldPathLength', mb_strlen($oldPath->value()) + 1)
            ->setParameter('oldPathPrefix', $oldPath->value().CategoryPathGenerator::PATH_SEPARATOR.'%')
            ->getQuery()
            ->execute();
    }
}
