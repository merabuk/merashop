<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

use App\IdentityAccess\Domain\Enum\GrantTypeEnum;

interface CredentialsInterface
{
    public function getGrantType(): GrantTypeEnum;
}
