<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\DTO\UserCredentialsInterface;
use App\IdentityAccess\Application\Exceptions\BadCredentialsException;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountEmailException;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Infrastructure\Security\AuthSubject;

class PasswordGrantHandler implements GrantHandlerInterface
{
    public function __construct(
        private readonly UserAccountReadRepositoryInterface $userAccountReadRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly TokenGeneratorInterface $tokenGenerator,
        private readonly int $expiresIn = 3600,
    ) {
    }

    public function supports(GrantTypeEnum $grantType): bool
    {
        return GrantTypeEnum::Password === $grantType;
    }

    /**
     * @throws InvalidUserAccountEmailException
     * @throws BadCredentialsException
     */
    public function handle(UserCredentialsInterface $data): TokenResponseData
    {
        $user = $this->userAccountReadRepository->findByEmail(
            EmailAddress::fromString($data->getUsername())
        );

        if (null === $user) {
            throw BadCredentialsException::becauseInvalidCredentials();
        }

        // TODO: find out how pass entity to password hasher
        if (!$this->passwordHasher->verify($user->getPasswordHash()->value(), $data->getPassword())) {
            throw BadCredentialsException::becauseInvalidCredentials();
        }

        $grantResult = new GrantResultData(
            subjectUlid: $user->getUlid()->value(),
            roles: $user->getRoles()->toStrings(),
            scopes: [],
            expiresIn: $this->expiresIn,
            type: 'user'
        );

        $accessToken = $this->tokenGenerator->generateAccessToken($grantResult);
        $refreshToken = $this->tokenGenerator->generateRefreshToken($grantResult);

        return new TokenResponseData(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresIn: $this->expiresIn,
            tokenType: 'Bearer',
        );
    }
}
