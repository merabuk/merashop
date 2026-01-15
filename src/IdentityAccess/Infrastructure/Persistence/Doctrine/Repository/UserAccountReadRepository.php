<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmUserAccount;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;

class UserAccountReadRepository extends BaseUserAccountRepository implements UserAccountReadRepositoryInterface
{
    /**
     * @throws InvalidIdentityAccessValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    public function findById(int $id): ?UserAccount
    {
        $ormUser = $this->find($id);

        return $this->checkAndMapToDomain($ormUser);
    }

    /**
     * @throws InvalidIdentityAccessValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    public function findByUlid(Ulid $ulid): ?UserAccount
    {
        $ormUser = $this->findOneBy(['ulid' => $ulid->value()]);

        return $this->checkAndMapToDomain($ormUser);
    }

    /**
     * @throws InvalidIdentityAccessValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    public function findByEmail(EmailAddress $email): ?UserAccount
    {
        $ormUser = $this->findOneBy(['email' => $email->value()]);

        return $this->checkAndMapToDomain($ormUser);
    }

    /**
     * @throws InvalidIdentityAccessValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    private function checkAndMapToDomain(?object $ormUser): ?UserAccount
    {
        if (false === $ormUser instanceof OrmUserAccount) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($ormUser);
    }
}
