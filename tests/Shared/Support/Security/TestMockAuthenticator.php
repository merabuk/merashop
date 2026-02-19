<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Security;

use App\Shared\Application\Security\AuthEntityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class TestMockAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly AuthEntityContextInterface $authContext,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return null !== $this->authContext->getIdentity();
    }

    public function authenticate(Request $request): Passport
    {
        $identity = $this->authContext->getIdentity();

        return new SelfValidatingPassport(
            new UserBadge($identity->id, function () use ($identity) {
                return new InMemoryUser($identity->id, null, $identity->roles);
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
