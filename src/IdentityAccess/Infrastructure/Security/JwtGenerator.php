<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\Exception\TokenGenerateException;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use Lcobucci\JWT\Configuration;
use Random\RandomException;
use Symfony\Component\Clock\ClockInterface;
use Throwable;

class JwtGenerator implements TokenGeneratorInterface
{
    public function __construct(
        private readonly Configuration $jwtConfiguration,
        private readonly ClockInterface $clock,
        private readonly string $appName,
        private readonly int $ttl,
    ) {
    }

    /**
     * @throws TokenGenerateException
     */
    public function generateAccessToken(GrantResultData $grantResultData): AccessTokenData
    {
        try {
            $now = $this->clock->now();
            $accessTokenExpiresAt = $now->modify(sprintf('+%d seconds', $this->ttl));

            $builder = $this->jwtConfiguration->builder()
                ->issuedBy($this->appName)
                ->identifiedBy($this->generateIdentifier())
                ->issuedAt($now)
                ->canOnlyBeUsedAfter($now)
                ->expiresAt($accessTokenExpiresAt)
                ->relatedTo($grantResultData->subjectUlid)
                ->withClaim('roles', $grantResultData->roles)
                ->withClaim('scopes', $grantResultData->scopes)
                ->withClaim('sub_type', $grantResultData->subjectType->value);

            $token = $builder->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());

            return new AccessTokenData(token: $token->toString(), expiresIn: $this->ttl);
        } catch (Throwable $e) {
            throw new TokenGenerateException('Failed to generate access token', previous: $e);
        }
    }

    /**
     * @throws RandomException
     */
    private function generateIdentifier(): string
    {
        return bin2hex(random_bytes(32));
    }
}
