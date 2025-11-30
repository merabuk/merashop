<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Infrastructure\Persistence\Doctrine\DoctrineRepositoryTrait;
use App\Users\Domain\Entity\User;
use App\Users\Domain\Repository\UserRepositoryInterface;
use App\Users\Infrastructure\Persistence\Doctrine\Entity\OrmUser;
use App\Users\Infrastructure\Persistence\Doctrine\Mapper\UserMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

class UserRepository implements UserRepositoryInterface
{
    use DoctrineRepositoryTrait;

    const ORM_ENTITY_CLASS_NAME = OrmUser::class;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserMapper $mapper
    ) {
    }

    public function findById(int $id): ?User
    {
        return $this->_findOneById($id);
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
