<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\Repository\UserAccountWriteRepositoryInterface;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;

final class UserAccountWriteRepository extends BaseUserAccountRepository implements UserAccountWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws EntityIdMissingException
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
     * @throws IncompatibleMappedEntityException
     */
    public function delete(UserAccount $userAccount): void
    {
        $this->_delete($userAccount);
    }
}
