<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Repository\RefreshTokenWriteRepositoryInterface;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ThrowableValueObjectException;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;

final class RefreshTokenWriteRepository extends BaseRefreshTokenRepository implements RefreshTokenWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws ThrowableValueObjectException
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     */
    public function save(RefreshToken $refreshToken): RefreshToken
    {
        return $this->_save(domain: $refreshToken, id: $refreshToken->getId()?->value());
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function delete(RefreshToken $refreshToken): void
    {
        $this->_delete($refreshToken);
    }
}
