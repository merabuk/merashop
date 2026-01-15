<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Repository;

use App\IdentityAccess\Domain\Entity\RefreshToken;

interface RefreshTokenWriteRepositoryInterface
{
    public function save(RefreshToken $refreshToken): RefreshToken;

    public function delete(RefreshToken $refreshToken): void;
}
