<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Authenticator;

use App\IdentityAccess\Application\Security\CurrentAccessTokenContextInterface;
use App\IdentityAccess\Domain\Security\AccessTokenBlacklistInterface;
use App\IdentityAccess\Infrastructure\Exception\InvalidCredentialsException;
use App\IdentityAccess\Infrastructure\Security\Provider\AuthEntityProvider;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use DateTimeInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\RegisteredClaims;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Throwable;

final readonly class JwtAccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private Configuration $jwtConfiguration,
        private AccessTokenBlacklistInterface $blacklist,
        private CurrentAccessTokenContextInterface $accessTokenContext,
    ) {
    }

    /**
     * @throws InvalidCredentialsException
     */
    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        try {
            $token = $this->jwtConfiguration->parser()->parse($accessToken);
        } catch (Throwable) {
            throw new InvalidCredentialsException('Invalid JWT token');
        }

        if (false === $token instanceof Plain) {
            throw new InvalidCredentialsException('Invalid JWT token type');
        }

        if (false === $this->jwtConfiguration->validator()->validate($token, ...$this->jwtConfiguration->validationConstraints())) {
            throw new InvalidCredentialsException('JWT token validation failed');
        }

        $claims = $token->claims();

        $jti = $claims->get(RegisteredClaims::ID);
        if ($jti && $this->blacklist->isRevoked((string) $jti)) {
            throw new InvalidCredentialsException('Token has been revoked');
        }

        $ulid = $claims->get(RegisteredClaims::SUBJECT);
        $type = IdentityTypeEnum::tryFrom((string) $claims->get('sub_type'));

        if (null === $ulid) {
            throw new InvalidCredentialsException('JWT token does not contain a subject (ULID)');
        }

        if (null === $type) {
            throw new InvalidCredentialsException('JWT token does not contain a subject type');
        }

        $expiresAt = $claims->get(RegisteredClaims::EXPIRATION_TIME);

        if ($jti && $expiresAt instanceof DateTimeInterface) {
            $this->accessTokenContext->set((string) $jti, $expiresAt->getTimestamp());
        } else {
            throw new InvalidCredentialsException(sprintf('Token missing required claims (%s, %s)', RegisteredClaims::ID, RegisteredClaims::EXPIRATION_TIME));
        }

        return new UserBadge(userIdentifier: $type->value.AuthEntityProvider::SEPARATOR.$ulid);
    }
}
