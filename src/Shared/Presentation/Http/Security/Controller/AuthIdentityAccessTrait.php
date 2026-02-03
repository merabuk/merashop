<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Security\Controller;

use App\Shared\Application\Security\AuthIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait AuthIdentityAccessTrait
{
    protected function denyAccessUnlessNotUser(AuthIdentity $identity): void
    {
        if (false === $identity->isUser()) {
            // TODO: rework on custom exception
            throw new AccessDeniedException(sprintf("Auth entity type is not user. Auth type: '%s'", $identity->type->value));
        }
    }
}
