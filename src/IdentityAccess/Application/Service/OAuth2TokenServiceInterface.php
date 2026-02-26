<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Service;

use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\Exception\UnsupportedGrantTypeException;

interface OAuth2TokenServiceInterface
{
    /**
     * @throws UnsupportedGrantTypeException
     */
    public function handle(OAuth2Data $data): TokenResponseData;
}
