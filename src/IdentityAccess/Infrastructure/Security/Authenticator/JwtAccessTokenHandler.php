<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Authenticator;

use App\IdentityAccess\Application\Security\CurrentAccessTokenContextInterface;
use App\IdentityAccess\Domain\Security\AccessTokenBlacklistInterface;
use App\IdentityAccess\Infrastructure\Exception\InvalidCredentialsException;
use App\IdentityAccess\Infrastructure\Security\Jwt\JwtConfigFactory;
use App\IdentityAccess\Infrastructure\Security\Provider\AuthEntityProvider;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Helpers\TypeCastingTrait;
use DateTimeInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\UnencryptedToken;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Throwable;

final readonly class JwtAccessTokenHandler implements AccessTokenHandlerInterface
{
    use TypeCastingTrait;

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
            $accessToken = self::castToNonEmptyString(string: $accessToken, message: 'Giving access token is empty');

            $token = $this->jwtConfiguration->parser()->parse($accessToken);
        } catch (Throwable) {
            throw new InvalidCredentialsException('Invalid JWT token');
        }

        if (false === $token instanceof UnencryptedToken) {
            throw new InvalidCredentialsException('Invalid JWT token type');
        }

        if (false === $this->jwtConfiguration->validator()->validate($token, ...$this->jwtConfiguration->validationConstraints())) {
            throw new InvalidCredentialsException('JWT token validation failed');
        }

        $claims = $token->claims();

        $jti = self::castToString(value: $claims->get(RegisteredClaims::ID));
        if ($jti && $this->blacklist->isRevoked($jti)) {
            throw new InvalidCredentialsException('Token has been revoked');
        }

        $ulid = self::castToNullableString(value: $claims->get(RegisteredClaims::SUBJECT));
        $type = IdentityTypeEnum::tryFrom(value: self::castToString(
            value: $claims->get(JwtConfigFactory::CLAIM_SUBJECT_TYPE)
        ));

        if (null === $ulid) {
            throw new InvalidCredentialsException('JWT token does not contain a subject (ULID)');
        }

        if (null === $type) {
            throw new InvalidCredentialsException('JWT token does not contain a subject type');
        }

        $expiresAt = $claims->get(RegisteredClaims::EXPIRATION_TIME);

        if ($jti && $expiresAt instanceof DateTimeInterface) {
            $this->accessTokenContext->set($jti, $expiresAt->getTimestamp());
        } else {
            throw new InvalidCredentialsException(sprintf('Token missing required claims (%s, %s)', RegisteredClaims::ID, RegisteredClaims::EXPIRATION_TIME));
        }

        return new UserBadge(userIdentifier: $type->value.AuthEntityProvider::SEPARATOR.$ulid);
    }
}
