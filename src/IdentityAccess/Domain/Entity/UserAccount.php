<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Entity;

use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\UserAccount\PasswordHash;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use App\IdentityAccess\Domain\ValueObject\Ulid;

class UserAccount
{
    public function __construct(
        private readonly Ulid $ulid,
        private EmailAddress $email,
        private PasswordHash $passwordHash,
        private RoleCollection $roles,
    ) {
    }

    public function getUlid(): Ulid
    {
        return $this->ulid;
    }

    public function getEmail(): EmailAddress
    {
        return $this->email;
    }

    public function getPasswordHash(): PasswordHash
    {
        return $this->passwordHash;
    }

    public function getRoles(): RoleCollection
    {
        return $this->roles;
    }

    public function changeEmail(EmailAddress $email): void
    {
        $this->email = $email;
    }

    public function changePasswordHash(PasswordHash $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function updateRoles(RoleCollection $roles): void
    {
        $this->roles = $roles;
    }
}
