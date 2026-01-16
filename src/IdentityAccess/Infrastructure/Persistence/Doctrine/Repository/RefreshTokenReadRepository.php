<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Repository\RefreshTokenReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmRefreshToken;

final class RefreshTokenReadRepository extends BaseRefreshTokenRepository implements RefreshTokenReadRepositoryInterface
{
    public function findByToken(TokenHash $token): ?RefreshToken
    {
        $orm = $this->findOneBy(['token' => $token->value()]);

        return $this->checkAndMapToDomain($orm);
    }

    private function checkAndMapToDomain(?object $ormRefreshToken): ?RefreshToken
    {
        if (false === $ormRefreshToken instanceof OrmRefreshToken) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($ormRefreshToken);
    }
}
