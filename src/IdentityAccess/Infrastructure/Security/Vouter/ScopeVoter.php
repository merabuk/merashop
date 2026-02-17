<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Vouter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<string, mixed>
 */
final class ScopeVoter extends Voter
{
    private const string PREFIX = 'SCOPE_';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return str_starts_with($attribute, self::PREFIX);
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
