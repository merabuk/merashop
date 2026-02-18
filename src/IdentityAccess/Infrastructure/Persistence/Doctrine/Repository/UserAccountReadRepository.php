<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Id;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Ulid;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmUserAccount;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\ReadRepositoryTrait;

class UserAccountReadRepository extends BaseUserAccountRepository implements UserAccountReadRepositoryInterface
{
    use ReadRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws InvalidIdentityAccessValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    public function findById(Id $id): ?UserAccount
    {
        $ormUser = $this->find($id->value());

        return $this->checkAndMapToDomain($ormUser);
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidIdentityAccessValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    public function findByEmail(EmailAddress $email): ?UserAccount
    {
        $ormUser = $this->findOneBy(['email' => $email->value()]);

        return $this->checkAndMapToDomain($ormUser);
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidIdentityAccessValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    public function findByUlid(Ulid $ulid): ?UserAccount
    {
        $ormUser = $this->findOneBy(['ulid' => $ulid->value()]);

        return $this->checkAndMapToDomain($ormUser);
    }

    public function existsByEmail(EmailAddress $email): bool
    {
        return $this->_existsBy([
            $this->_makeCriterion(field: 'email', value: $email->value()),
        ]);
    }

    /**
     * @throws EntityIdMissingException
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
