<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Service;

use App\IdentityAccess\Application\DTO\RefreshTokenData;
use App\IdentityAccess\Application\Exception\RefreshToken\CreateRefreshTokenException;
use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\Shared\Domain\Enum\IdentityTypeEnum;

interface RefreshTokenServiceInterface
{
    /**
     * @throws CreateRefreshTokenException
     */
    public function create(string $accountUlid, IdentityTypeEnum $accountType): RefreshTokenData;

    public function revoke(RefreshToken $refreshToken): void;
}
