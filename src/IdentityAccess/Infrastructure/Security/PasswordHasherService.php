<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

readonly class PasswordHasherService implements PasswordHasherInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function hash(string $plainPassword): string
    {
        return $this->passwordHasher->hashPassword(user: $this->createDummyUser(), plainPassword: $plainPassword);
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid(user: $this->createDummyUser(), plainPassword: $plainPassword);
    }

    private function createDummyUser(?string $hashedPassword = null): PasswordAuthenticatedUserInterface
    {
        return new class implements PasswordAuthenticatedUserInterface {
            public function getPassword(): ?string
            {
                return null;
            }
        };
    }
}
