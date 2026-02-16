<?php

declare(strict_types=1);

namespace App\Shared\Application\Security;

use App\Shared\Domain\Enum\IdentityTypeEnum;

final readonly class AuthIdentity
{
    public function __construct(
        public string $id,
        public IdentityTypeEnum $type,
        /** @var string[] */
        public array $roles,
    ) {
    }

    public function isUser(): bool
    {
        return IdentityTypeEnum::User === $this->type;
    }

    public function isModule(): bool
    {
        return IdentityTypeEnum::Module === $this->type;
    }

    public function isAdmin(): bool
    {
        return IdentityTypeEnum::Admin === $this->type;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }
}
