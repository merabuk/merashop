<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

use App\Shared\Domain\Enum\IdentityTypeEnum;

interface UserCredentialsInterface extends CredentialsInterface
{
    public function getUsername(): string;

    public function getPassword(): string;

    public function getAccountType(): IdentityTypeEnum;
}
