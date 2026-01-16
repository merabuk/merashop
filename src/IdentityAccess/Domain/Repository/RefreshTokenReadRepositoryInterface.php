<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Repository;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;

interface RefreshTokenReadRepositoryInterface
{
    public function findByToken(TokenHash $token): ?RefreshToken;
}
