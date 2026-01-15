<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

interface UserCredentialsInterface extends CredentialsInterface
{
    public function getUsername(): string;

    public function getPassword(): string;
}
