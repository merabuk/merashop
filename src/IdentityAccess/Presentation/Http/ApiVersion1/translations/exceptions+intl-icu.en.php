<?php

declare(strict_types=1);

use App\IdentityAccess\Domain\Enum\ErrorCodeEnum;

return [
    ErrorCodeEnum::UserAccountAlreadyExists->value => 'User account already exists',
    ErrorCodeEnum::AdminAccountAlreadyExists->value => 'Admin account already exists',
];
