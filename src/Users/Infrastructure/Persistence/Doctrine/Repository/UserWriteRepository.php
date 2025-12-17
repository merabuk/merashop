<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ThrowableValueObjectException;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use App\Users\Domain\Entity\User;
use App\Users\Domain\Exception\InvalidUserValueObjectException;
use App\Users\Domain\Repository\UserWriteRepositoryInterface;
use Doctrine\ORM\Exception\ORMException;

class UserWriteRepository extends BaseUserRepository implements UserWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidUserValueObjectException
     * @throws ORMException
     * @throws ThrowableValueObjectException
     */
    public function save(User $user): User
    {
        return $this->_save(domain: $user, id: $user->getId()?->value());
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function delete(User $user): void
    {
        $this->_delete($user);
    }
}
