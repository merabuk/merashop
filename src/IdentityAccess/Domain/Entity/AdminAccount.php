<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Entity;

use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Id;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordHash;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Status;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;

class AdminAccount
{
    public function __construct(
        private readonly ?Id  $id,
        private readonly Ulid $ulid,
        private EmailAddress $email,
        private PasswordHash $passwordHash,
        private RoleCollection $roles,
        private Status $status,
    ) {}

    public static function create(
        Ulid $ulid,
        EmailAddress $email,
        PasswordHash $password,
        RoleCollection $roles,
        ?Status $status
    ): self {
        return new self(
            id: null,
            ulid: $ulid,
            email: $email,
            passwordHash: $password,
            roles: $roles,
            status: $status ?? Status::draft()
        );
    }

    public function getId(): ?Id
    {
        return $this->id;
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

    public function getStatus(): Status
    {
        return $this->status;
    }
}
