<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Repository\RefreshTokenWriteRepositoryInterface;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Type\RefreshToken\AccountType as DbalAccountType;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class RefreshTokenWriteRepository extends BaseRefreshTokenRepository implements RefreshTokenWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws ValueObjectExceptionInterface
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     */
    public function save(RefreshToken $refreshToken): RefreshToken
    {
        $orm = $this->_save(domain: $refreshToken, id: $refreshToken->getId()?->value());

        return $this->mapper->fromDoctrineOrm($orm);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(RefreshToken $refreshToken): void
    {
        $this->_delete($refreshToken->getId()?->value());
    }

    public function deleteAllPrevious(RefreshToken $refreshToken): void
    {
        $this->createQueryBuilder('rt')
            ->delete()
            ->where('rt.accountUlid = :accountUlid')
            ->andWhere('rt.accountType = :accountType')
            ->setParameter('accountUlid', $refreshToken->getAccountUlid()->value(), UlidType::NAME)
            ->setParameter('accountType', $refreshToken->getAccountType()->value(), DbalAccountType::NAME)
            ->getQuery()
            ->execute();
    }
}
