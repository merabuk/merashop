<?php

declare(strict_types=1);

use App\IdentityAccess\Domain\Enum\ErrorCodeEnum;

return [
    ErrorCodeEnum::IdentityAccessDomainError->value => 'Something went wrong in the identity access domain. Please try again later',
    ErrorCodeEnum::UserAccountAlreadyExists->value => 'User account already exists',
    ErrorCodeEnum::AdminAccountAlreadyExists->value => 'Admin account already exists',
    ErrorCodeEnum::ModuleAccountAlreadyExists->value => 'Module account already exists',
];
