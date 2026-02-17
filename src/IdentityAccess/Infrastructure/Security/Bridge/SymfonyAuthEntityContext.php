<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Bridge;

use App\IdentityAccess\Infrastructure\Security\Provider\AuthSubject;
use App\Shared\Application\Security\AuthEntityContextInterface;
use App\Shared\Application\Security\AuthIdentity;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class SymfonyAuthEntityContext implements AuthEntityContextInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function getIdentity(): ?AuthIdentity
    {
        $user = $this->security->getUser();

        if (!$user instanceof AuthSubject) {
            return null;
        }

        return new AuthIdentity(
            id: $user->getUlid(),
            type: $user->getType(),
            roles: $user->getRoles()
        );
    }
}
