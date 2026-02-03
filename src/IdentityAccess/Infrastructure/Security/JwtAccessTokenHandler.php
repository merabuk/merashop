<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Infrastructure\Exception\InvalidCredentialsException;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\RegisteredClaims;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

final readonly class JwtAccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private Configuration $jwtConfiguration,
    ) {
    }

    /**
     * @throws InvalidCredentialsException
     */
    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        try {
            $token = $this->jwtConfiguration->parser()->parse($accessToken);
        } catch (\Throwable) {
            throw new InvalidCredentialsException('Invalid JWT token');
        }

        if (!$token instanceof Plain) {
            throw new InvalidCredentialsException('Invalid JWT token type');
        }

        if (!$this->jwtConfiguration->validator()->validate($token, ...$this->jwtConfiguration->validationConstraints())) {
            throw new InvalidCredentialsException('JWT token validation failed');
        }

        $claims = $token->claims();
        $ulid = $claims->get(RegisteredClaims::SUBJECT);
        $type = IdentityTypeEnum::tryFrom((string) $claims->get('sub_type'));

        if (null === $ulid) {
            throw new InvalidCredentialsException('JWT token does not contain a subject (ULID)');
        }

        if (null === $type) {
            throw new InvalidCredentialsException('JWT token does not contain a subject type');
        }

        return new UserBadge(userIdentifier: $type->value.':'.$ulid);
    }
}
