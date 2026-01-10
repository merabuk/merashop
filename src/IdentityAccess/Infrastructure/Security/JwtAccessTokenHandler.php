<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\Token\Plain;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

final readonly class JwtAccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private Configuration $jwtConfiguration,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        try {
            $token = $this->jwtConfiguration->parser()->parse($accessToken);
        } catch (\Exception) {
            throw new \Symfony\Component\Security\Core\Exception\BadCredentialsException('Invalid JWT token');
        }

        if (!$token instanceof Plain) {
            throw new \Symfony\Component\Security\Core\Exception\BadCredentialsException('Invalid JWT token type');
        }

        if (!$this->jwtConfiguration->validator()->validate($token, ...$this->jwtConfiguration->validationConstraints())) {
            throw new \Symfony\Component\Security\Core\Exception\BadCredentialsException('JWT token validation failed');
        }

        $claims = $token->claims();
        $ulid = $claims->get(RegisteredClaims::SUBJECT);

        if (null === $ulid) {
            throw new \Symfony\Component\Security\Core\Exception\BadCredentialsException('JWT token does not contain a subject (ULID)');
        }

        return new UserBadge($ulid);
    }
}
