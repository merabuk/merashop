<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\DTO\RefreshTokenInterface;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\Exceptions\CreateRefreshTokenException;
use App\IdentityAccess\Application\Exceptions\GrantHandlerException;
use App\IdentityAccess\Application\Exceptions\InvalidRefreshTokenException;
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
     * @throws GrantHandlerException
     */
    public function handle(RefreshTokenInterface $data): TokenResponseData
    {
        try {
            $tokenHash = $this->passwordHasher->hash($data->getRefreshToken());

            $refreshToken = $this->refreshTokenReadRepository->findByToken(
                TokenHash::fromString($tokenHash)
            );

            if (null === $refreshToken) {
                throw new InvalidRefreshTokenException();
            }

            if ($refreshToken->getExpiresAt()->isExpired()) {
                $this->refreshTokenService->revoke($refreshToken);

                throw new InvalidRefreshTokenException();
            }

            return match ($refreshToken->getAccountType()->value()) {
                AccountTypeEnum::User => $this->processUserAccount($refreshToken->getAccountUlid()->value()),
                default => throw new InvalidRefreshTokenException(),
            };
        } catch (CreateRefreshTokenException|InvalidUlidException|InvalidRefreshTokenTokenHashException $e) {
            throw new InvalidRefreshTokenException(previous: $e);
        }
    }

    /**
     * @throws CreateRefreshTokenException
     * @throws InvalidRefreshTokenException
     * @throws InvalidUlidException
     */
    private function processUserAccount(string $accountUlid): TokenResponseData
    {
        $accountType = AccountTypeEnum::User;
        $user = $this->userAccountReadRepository->findByUlid(Ulid::fromString($accountUlid));

        if (null === $user) {
            throw new InvalidRefreshTokenException();
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
