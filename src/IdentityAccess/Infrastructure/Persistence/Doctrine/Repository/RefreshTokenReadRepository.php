<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Repository\RefreshTokenReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Id;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmRefreshToken;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;

final class RefreshTokenReadRepository extends BaseRefreshTokenRepository implements RefreshTokenReadRepositoryInterface
{
    /**
     * @throws InvalidIdentityAccessValueObjectException
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     */
    public function findById(Id $id): ?RefreshToken
    {
        $orm = $this->find($id->value());

        return $this->checkAndMapToDomain($orm);
    }

    /**
     * @throws InvalidIdentityAccessValueObjectException
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     */
    public function findByToken(TokenHash $token): ?RefreshToken
    {
        $orm = $this->findOneBy(['token' => $token->value()]);

        return $this->checkAndMapToDomain($orm);
    }

    /**
     * @throws EntityFieldMissingException
     * @throws InvalidIdentityAccessValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    private function checkAndMapToDomain(?object $ormRefreshToken): ?RefreshToken
    {
        if (false === $ormRefreshToken instanceof OrmRefreshToken) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($ormRefreshToken);
    }
}
