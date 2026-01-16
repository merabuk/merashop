<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use Lcobucci\JWT\Configuration;
use Random\RandomException;
use Symfony\Component\Clock\Clock;

class JwtGenerator implements TokenGeneratorInterface
{
    public function __construct(
        private readonly Configuration $jwtConfiguration,
        private readonly Clock $clock,
        private readonly string $appName,
        private readonly int $ttl,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function generateAccessToken(GrantResultData $grantResultData): AccessTokenData
    {
        $now = $this->clock->now();
        $accessTokenExpiresAt = $now->modify(sprintf('+%d seconds', $this->ttl));

        $builder = $this->jwtConfiguration->builder()
            ->issuedBy($this->appName)
            ->identifiedBy(bin2hex(random_bytes(32)))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($accessTokenExpiresAt)
            ->withClaim('sub', $grantResultData->subjectUlid)
            ->withClaim('roles', $grantResultData->roles)
            ->withClaim('scopes', $grantResultData->scopes)
            ->withClaim('sub_type', $grantResultData->subjectType->value);

        $token = $builder->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());

        return new AccessTokenData(token: $token->toString(), expiresIn: $this->ttl);
    }
}
