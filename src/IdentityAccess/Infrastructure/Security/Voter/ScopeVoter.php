<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Voter;

use App\Shared\Domain\Enum\ScopeEnum;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<string, mixed>
 */
final class ScopeVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, ScopeEnum::getValues(), true)) {
            return true;
        }

        return str_contains($attribute, ':') || str_contains($attribute, '.');
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return in_array($attribute, $user->getRoles(), true);
    }
}
