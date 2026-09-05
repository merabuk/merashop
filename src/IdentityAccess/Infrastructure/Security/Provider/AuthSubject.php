<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Provider;

use App\IdentityAccess\Domain\Entity\AdminAccount;
use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\Entity\UserAccount;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Enum\RoleEnum;
use App\Shared\Domain\Helpers\TypeCaster;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class AuthSubject implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @param non-empty-string $identifier
     * @param string[]         $roles
     */
    private function __construct(
        private IdentityTypeEnum $type,
        private string $ulid,
        private string $identifier,
        private string $passwordHash,
        private array $roles,
    ) {
    }

    public static function fromUserAccount(UserAccount $userAccount): self
    {
        return new self(
            type: IdentityTypeEnum::User,
            ulid: $userAccount->getUlid()->value(),
            identifier: TypeCaster::castToNonEmptyString(
                string: $userAccount->getEmail()->value(),
                message: 'Giving user email is empty'
            ),
            passwordHash: $userAccount->getPasswordHash()->value(),
            roles: $userAccount->getRoles()->toStrings(),
        );
    }

    public static function fromModuleAccount(ModuleAccount $moduleAccount): self
    {
        return new self(
            type: IdentityTypeEnum::Module,
            ulid: $moduleAccount->getUlid()->value(),
            identifier: TypeCaster::castToNonEmptyString(
                string: $moduleAccount->getClientId()->value(),
                message: 'Giving module client id is empty'
            ),
            passwordHash: $moduleAccount->getClientSecret()->value(),
            roles: array_unique([...$moduleAccount->getScopes()->toStrings(), RoleEnum::Module->value]),
        );
    }

    public static function fromAdminAccount(AdminAccount $adminAccount): self
    {
        return new self(
            type: IdentityTypeEnum::Admin,
            ulid: $adminAccount->getUlid()->value(),
            identifier: TypeCaster::castToNonEmptyString(
                string: $adminAccount->getEmail()->value(),
                message: 'Giving admin email is empty'
            ),
            passwordHash: $adminAccount->getPasswordHash()->value(),
            roles: $adminAccount->getRoles()->toStrings(),
        );
    }

    public function getType(): IdentityTypeEnum
    {
        return $this->type;
    }

    public function getUlid(): string
    {
        return $this->ulid;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }

    public function eraseCredentials(): void
    {
        // Not required for this type of authentication
    }

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }
}
