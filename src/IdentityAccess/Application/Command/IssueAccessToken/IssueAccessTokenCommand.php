<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Command\IssueAccessToken;

use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\Shared\Application\Command\CommandInterface;

class IssueAccessTokenCommand implements CommandInterface
{
    public function __construct(
        public OAuth2Data $data,
    ) {
    }
}
