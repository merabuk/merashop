<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Service;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\RefreshTokenReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\RefreshTokenWriteRepositoryInterface;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\ExpiresAt;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Token;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Infrastructure\Security\AuthSubject;
use App\IdentityAccess\Presentation\ApiVersion1\Request\AccessTokenRequest;
use Lcobucci\JWT\Configuration;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

final readonly class OAuth2TokenServiceJunie
{
    public function __construct(
        private UserAccountReadRepositoryInterface $userAccountRepository,
        private ModuleAccountReadRepositoryInterface $moduleAccountRepository,
        private RefreshTokenReadRepositoryInterface $refreshTokenReadRepository,
        private RefreshTokenWriteRepositoryInterface $refreshTokenWriteRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private Configuration $jwtConfiguration,
    ) {
    }

    public function handle(AccessTokenRequest $request): array
    {
        return match ($request->grant_type) {
            'password' => $this->handlePasswordGrant($request),
            'client_credentials' => $this->handleClientCredentialsGrant($request),
            'refresh_token' => $this->handleRefreshTokenGrant($request),
            default => throw new \InvalidArgumentException('Unsupported grant type'),
        };
    }

    private function handlePasswordGrant(AccessTokenRequest $request): array
    {
        if (!$request->username || !$request->password) {
            throw new \InvalidArgumentException('Username and password are required for password grant');
        }

        $user = $this->userAccountRepository->findByEmail(EmailAddress::fromString($request->username));

        if (!$user) {
            throw new BadCredentialsException('Invalid credentials');
        }

        $authSubject = AuthSubject::fromUserAccount($user);
        if (!$this->passwordHasher->isPasswordValid($authSubject, $request->password)) {
            throw new BadCredentialsException('Invalid credentials');
        }

        return $this->generateTokens($user->getUlid(), $user->getRoles()->toStrings());
    }

    private function handleClientCredentialsGrant(AccessTokenRequest $request): array
    {
        if (!$request->client_id || !$request->client_secret) {
            throw new \InvalidArgumentException('Client ID and secret are required for client_credentials grant');
        }

        $module = $this->moduleAccountRepository->findByClientId(ClientId::fromString($request->client_id));

        if (!$module) {
            throw new BadCredentialsException('Invalid client credentials');
        }

        $authSubject = AuthSubject::fromModuleAccount($module);
        if (!$this->passwordHasher->isPasswordValid($authSubject, $request->client_secret)) {
            throw new BadCredentialsException('Invalid client credentials');
        }

        return $this->generateTokens($module->getUlid(), $authSubject->getRoles(), false);
    }

    private function handleRefreshTokenGrant(AccessTokenRequest $request): array
    {
        if (!$request->refresh_token) {
            throw new \InvalidArgumentException('Refresh token is required');
        }

        $storedToken = $this->refreshTokenReadRepository->findByToken(Token::fromString($request->refresh_token));

        if (!$storedToken || $storedToken->getExpiresAt()->isExpired()) {
            if ($storedToken) {
                $this->refreshTokenWriteRepository->delete($storedToken);
            }
            throw new BadCredentialsException('Invalid or expired refresh token');
        }

        $accountUlid = $storedToken->getAccountUlid();

        $roles = [];
        $user = $this->userAccountRepository->findByUlid($accountUlid);
        if ($user) {
            $roles = $user->getRoles()->toStrings();
        } else {
            $module = $this->moduleAccountRepository->findByUlid($accountUlid);
            if ($module) {
                $roles = AuthSubject::fromModuleAccount($module)->getRoles();
            } else {
                throw new BadCredentialsException('Account not found');
            }
        }

        $this->refreshTokenWriteRepository->delete($storedToken);

        return $this->generateTokens($accountUlid, $roles);
    }

    private function generateTokens(Ulid $accountUlid, array $roles, bool $withRefreshToken = true): array
    {
        $now = new \DateTimeImmutable();
        $accessTokenExpiresAt = $now->modify('+1 hour');

        $builder = $this->jwtConfiguration->builder()
            ->issuedBy('merashop')
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($accessTokenExpiresAt)
            ->withClaim('sub', $accountUlid->value())
            ->withClaim('roles', $roles);

        $token = $builder->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());

        $response = [
            'access_token' => $token->toString(),
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];

        if ($withRefreshToken) {
            $refreshTokenValue = bin2hex(random_bytes(40));
            $refreshTokenExpiresAt = $now->modify('+30 days');

            $refreshToken = new RefreshToken(
                token: Token::fromString($refreshTokenValue),
                accountUlid: $accountUlid,
                expiresAt: new ExpiresAt($refreshTokenExpiresAt)
            );

            $this->refreshTokenWriteRepository->save($refreshToken);

            $response['refresh_token'] = $refreshTokenValue;
        }

        return $response;
    }
}
