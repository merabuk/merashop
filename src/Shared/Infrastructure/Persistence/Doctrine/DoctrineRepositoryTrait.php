<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\Exception\ORMException;

trait DoctrineRepositoryTrait
{
    private function _findOneById(int $id): ?object
    {
        $doctrineObject = $this->entityManager->getRepository(static::ORM_ENTITY_CLASS_NAME)->find($id);

        return $this->getOneOrNothing($doctrineObject);
    }

    private function getOneOrNothing(?object $doctrineObject): ?object
    {
        return $doctrineObject ? $this->mapper->fromDoctrine($doctrineObject) : null;
    }

    private function _delete(object $domainObject): void
    {
        $this->entityManager->remove(
            $this->mapper->toDoctrine($domainObject)
        );
        $this->entityManager->flush();
    }

    /**
     * @throws ORMException
     */
    private function _save(object $domainObject): void
    {
        $doctrineObject = $this->mapper->toDoctrine($domainObject);

        if (is_null($doctrineObject->getId()) === false) {
            $doctrineReference = $this->entityManager->getReference(
                entityName:  static::ORM_ENTITY_CLASS_NAME,
                id: $doctrineObject->getId()
            );
            if ($this->entityManager->contains($doctrineReference) === false) {
                $this->entityManager->persist($doctrineReference);
            }
        } else {
            $this->entityManager->persist($doctrineObject);
        }

        $this->entityManager->flush();
        $domainObject->setId($doctrineObject->getId());
    }
}
