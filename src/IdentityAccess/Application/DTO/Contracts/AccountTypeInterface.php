<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO\Contracts;

use App\Shared\Domain\Enum\IdentityTypeEnum;

interface AccountTypeInterface
{
    public function getAccountType(): IdentityTypeEnum;
}
