<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\Repository\UserAccountWriteRepositoryInterface;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmUserAccount;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class UserAccountWriteRepository extends BaseUserAccountRepository implements UserAccountWriteRepositoryInterface
{
    /**
     * @use WriteRepositoryTrait<UserAccount, OrmUserAccount>
     */
    use WriteRepositoryTrait;

    /**
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     * @throws ValueObjectExceptionInterface
     */
    public function save(UserAccount $userAccount): UserAccount
    {
        $orm = $this->_save(domain: $userAccount, id: $userAccount->getId()?->value());

        return $this->mapper->fromDoctrineOrm($orm);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(UserAccount $userAccount): void
    {
        $this->_delete($userAccount->getId()?->value());
    }
}
