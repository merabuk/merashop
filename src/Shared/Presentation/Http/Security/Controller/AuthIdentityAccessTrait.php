<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Security\Controller;

use App\Shared\Application\Security\AuthIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait AuthIdentityAccessTrait
{
    protected function denyAccessUnlessUser(AuthIdentity $identity): void
    {
        if (false === $identity->isUser()) {
            // TODO: rework on custom exception
            throw $this->makeAccessDeniedException(sprintf("Auth entity type is not user. Auth type: '%s'", $identity->type->value));
        }
    }

    protected function denyAccessUnlessAdmin(AuthIdentity $identity): void
    {
        if (false === $identity->isAdmin()) {
            throw $this->makeAccessDeniedException(sprintf("Auth entity type is not admin. Auth type: '%s'", $identity->type->value));
        }
    }

    protected function makeAccessDeniedException(string $message): AccessDeniedException
    {
        return new AccessDeniedException($message);
    }
}
