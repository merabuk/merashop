<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\Exception\TokenGenerateException;

interface TokenGeneratorInterface
{
    /**
     * @throws TokenGenerateException
     */
    public function generateAccessToken(GrantResultData $grantResultData): AccessTokenData;
}
