<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\AdminAccount;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Id;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmAdminAccount;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\ReadRepositoryTrait;

class AdminAccountReadRepository extends BaseAdminAccountRepository implements AdminAccountReadRepositoryInterface
{
    use ReadRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws InvalidIdentityAccessValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    public function findById(Id $id): ?AdminAccount
    {
        $ormUser = $this->find($id->value());

        return $this->checkAndMapToDomain($ormUser);
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidIdentityAccessValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    public function findByEmail(EmailAddress $email): ?AdminAccount
    {
        $ormUser = $this->findOneBy(['email' => $email->value()]);

        return $this->checkAndMapToDomain($ormUser);
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidIdentityAccessValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    public function findByUlid(Ulid $ulid): ?AdminAccount
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
    private function checkAndMapToDomain(?object $ormUser): ?AdminAccount
    {
        if (false === $ormUser instanceof OrmAdminAccount) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($ormUser);
    }
}
