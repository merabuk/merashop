<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\GrantResultData;

interface TokenGeneratorInterface
{
    public function generateAccessToken(GrantResultData $grantResultData): AccessTokenData;
}
