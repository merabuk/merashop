<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

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
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function generateAccessToken(GrantResultData $grantResultData): string
    {
        $now = $this->clock->now();
        // TODO: Config modifier through environment variable
        $accessTokenExpiresAt = $now->modify(sprintf('+%d seconds', $grantResultData->expiresIn));

        $builder = $this->jwtConfiguration->builder()
            ->issuedBy('MeraShop') // TODO: Find out to set AppName through environment variable
            ->identifiedBy(bin2hex(random_bytes(32)))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($accessTokenExpiresAt)
            ->withClaim('sub', $grantResultData->subjectUlid)
            ->withClaim('roles', $grantResultData->roles)
            ->withClaim('scopes', $grantResultData->scopes)
            ->withClaim('type', $grantResultData->type);

        $token = $builder->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());

        return $token->toString();
    }

    public function generateRefreshToken(GrantResultData $grantResultData): string
    {
        // TODO: Implement generateRefreshToken() method.
    }
}
