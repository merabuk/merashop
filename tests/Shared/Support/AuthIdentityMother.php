<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support;

use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Enum\RoleEnum;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;

final readonly class AuthIdentityMother
{
    public function __construct(
        private UlidGeneratorInterface $ulidGenerator,
    ) {
    }

    public function admin(?string $id = null, array $roles = [RoleEnum::Admin->value]): AuthIdentity
    {
        return new AuthIdentity(
            id: $id ?? $this->ulidGenerator->next(),
            type: IdentityTypeEnum::Admin,
            roles: $roles
        );
    }

    public function user(?string $id = null, array $roles = [RoleEnum::User->value]): AuthIdentity
    {
        return new AuthIdentity(
            id: $id ?? $this->ulidGenerator->next(),
            type: IdentityTypeEnum::User,
            roles: $roles
        );
    }
}
