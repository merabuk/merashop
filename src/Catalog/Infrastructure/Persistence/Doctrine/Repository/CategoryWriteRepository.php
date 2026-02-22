<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Domain\Service\CategoryPathGenerator;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategory;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;
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
        $orm = $this->_save(domain: $category, id: $category->getId()?->value());

        return $this->mapper->fromDoctrineOrm($orm);
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

    protected function findOrmForUpdateFallback(string $stringId): ?OrmCategory
    {
        $orm = $this->getEntityManager()->createQueryBuilder()
            ->select('c', 't', 'p')
            ->from(OrmCategory::class, 'c')
            ->leftJoin('c.translations', 't')
            ->leftJoin('c.parent', 'p')
            ->where('c.id = :id')
            ->setParameter('id', $stringId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($orm instanceof OrmCategory) {
            return $orm;
        }

        return null;
    }
}
