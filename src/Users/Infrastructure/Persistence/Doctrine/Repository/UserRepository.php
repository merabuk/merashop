<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepositoryTrait;
use App\Users\Domain\Entity\User;
use App\Users\Domain\Repository\UserRepositoryInterface;
use App\Users\Infrastructure\Persistence\Doctrine\Entity\OrmUser;
use App\Users\Infrastructure\Persistence\Doctrine\Mapper\UserMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    use DoctrineRepositoryTrait;

    public const ORM_ENTITY_CLASS_NAME = OrmUser::class;

    public function __construct(
        ManagerRegistry $registry,
        private readonly UserMapper $mapper,
    ) {
        parent::__construct(registry: $registry, entityClass: self::ORM_ENTITY_CLASS_NAME);
    }

    public function findById(int $id): ?User
    {
        $ormUser = $this->find($id);

        if (false === $ormUser instanceof OrmUser) {
            return null;
        }

        return $this->mapper->fromDoctrine($ormUser);
    }

    /**
     * @throws ORMException
     */
    public function save(User $user): void
    {
        $this->_save($user);
    }

    public function delete(User $user): void
    {
        $this->_delete($user);
    }
}
