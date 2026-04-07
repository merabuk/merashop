<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeStateException;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class AttributeWriteRepository extends BaseAttributeRepository implements AttributeWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws AttributeStateException
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     * @throws ValueObjectExceptionInterface
     */
    public function save(Attribute $attribute): Attribute
    {
        $orm = $this->_save(domain: $attribute, id: $attribute->getId()?->value());

        return $this->mapper->fromDoctrineOrm($orm);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(Attribute $attribute): void
    {
        $this->_delete($attribute->getId()?->value());
    }
}
