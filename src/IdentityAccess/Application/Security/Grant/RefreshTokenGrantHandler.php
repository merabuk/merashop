<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\DTO\RefreshTokenInterface;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\Exceptions\BadCredentialsException;
use App\IdentityAccess\Application\Exceptions\CreateRefreshTokenException;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use App\IdentityAccess\Application\Service\RefreshTokenService;
use App\IdentityAccess\Domain\Enum\AccountTypeEnum;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenTokenHashException;
use App\IdentityAccess\Domain\Repository\RefreshTokenReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid;

readonly class RefreshTokenGrantHandler implements GrantHandlerInterface
{
    public function __construct(
        private RefreshTokenReadRepositoryInterface $refreshTokenReadRepository,
        private UserAccountReadRepositoryInterface $userAccountReadRepository,
        private PasswordHasherInterface $passwordHasher,
        private TokenGeneratorInterface $tokenGenerator,
        private RefreshTokenService $refreshTokenService,
    ) {
    }

    public function supports(GrantTypeEnum $grantType): bool
    {
        return GrantTypeEnum::RefreshToken === $grantType;
    }

    /**
     * @throws BadCredentialsException
     */
    public function handle(RefreshTokenInterface $data): TokenResponseData
    {
        try {
            $tokenHash = $this->passwordHasher->hash($data->getRefreshToken());

            $refreshToken = $this->refreshTokenReadRepository->findByToken(
                TokenHash::fromString($tokenHash)
            );

            if (null === $refreshToken) {
                throw BadCredentialsException::becauseInvalidRefreshToken();
            }

            if ($refreshToken->getExpiresAt()->isExpired()) {
                $this->refreshTokenService->revoke($refreshToken);

                throw BadCredentialsException::becauseInvalidRefreshToken();
            }

            return match ($refreshToken->getAccountType()->value()) {
                AccountTypeEnum::User => $this->processUserAccount($refreshToken->getAccountUlid()->value()),
                default => throw BadCredentialsException::becauseInvalidRefreshToken(),
            };
        } catch (CreateRefreshTokenException|InvalidUlidException|InvalidRefreshTokenTokenHashException $e) {
            throw BadCredentialsException::becauseInvalidRefreshToken(previous: $e);
        }
    }

    /**
     * @throws BadCredentialsException
     * @throws CreateRefreshTokenException
     * @throws InvalidUlidException
     */
    private function processUserAccount(string $accountUlid): TokenResponseData
    {
        $accountType = AccountTypeEnum::User;
        $user = $this->userAccountReadRepository->findByUlid(Ulid::fromString($accountUlid));

        if (null === $user) {
            throw BadCredentialsException::becauseInvalidRefreshToken();
        }

        $grantResult = new GrantResultData(
            subjectUlid: $user->getUlid()->value(),
            subjectType: $accountType,
            roles: $user->getRoles()->toStrings(),
            scopes: [],
        );

        $accessTokenData = $this->tokenGenerator->generateAccessToken($grantResult);
        $refreshTokenData = $this->refreshTokenService->create($user->getUlid()->value(), $accountType);

        return new TokenResponseData(accessTokenData: $accessTokenData, refreshTokenData: $refreshTokenData);
    }
}
