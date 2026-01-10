<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\Entity\UserAccount;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class AuthSubject implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @param string[] $roles
     */
    private function __construct(
        private string $ulid,
        private string $identifier,
        private string $passwordHash,
        private array $roles,
    ) {
    }

    public static function fromUserAccount(UserAccount $userAccount): self
    {
        return new self(
            ulid: $userAccount->getUlid()->value(),
            identifier: $userAccount->getEmail()->value(),
            passwordHash: $userAccount->getPasswordHash()->value(),
            roles: $userAccount->getRoles()->toStrings(),
        );
    }

    public static function fromModuleAccount(ModuleAccount $moduleAccount): self
    {
        $roles = array_map(
            static fn (string $scope) => 'SCOPE_'.str_replace([':', ' '], '_', strtoupper($scope)),
            $moduleAccount->getScopes()->toStrings()
        );

        return new self(
            ulid: $moduleAccount->getUlid()->value(),
            identifier: $moduleAccount->getClientId()->value(),
            passwordHash: $moduleAccount->getClientSecret()->value(),
            roles: array_unique([...$roles, 'ROLE_MODULE']),
        );
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

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }
}
