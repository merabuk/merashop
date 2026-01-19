<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\DTO\RefreshTokenData;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\DTO\UserCredentialsInterface;
use App\IdentityAccess\Application\Exceptions\CreateRefreshTokenException;
use App\IdentityAccess\Application\Exceptions\GrantHandlerException;
use App\IdentityAccess\Application\Exceptions\InvalidCredentialsException;
use App\IdentityAccess\Application\Exceptions\TokenGenerateException;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use App\IdentityAccess\Application\Service\RefreshTokenService;
use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\Enum\AccountTypeEnum;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountEmailException;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;

readonly class PasswordGrantHandler implements GrantHandlerInterface
{
    private AccountTypeEnum $accountType;

    public function __construct(
        private UserAccountReadRepositoryInterface $userAccountReadRepository,
        private PasswordHasherInterface $passwordHasher,
        private TokenGeneratorInterface $tokenGenerator,
        private RefreshTokenService $refreshTokenService,
    ) {
        $this->accountType = AccountTypeEnum::User;
    }

    public function supports(GrantTypeEnum $grantType): bool
    {
        return GrantTypeEnum::Password === $grantType;
    }

    /**
     * @throws GrantHandlerException
     */
    public function handle(UserCredentialsInterface $data): TokenResponseData
    {
        try {
            $user = $this->userAccountReadRepository->findByEmail(
                EmailAddress::fromString($data->getUsername())
            );

            if (
                null === $user
                || !$this->passwordHasher->verify(
                    hashedPassword: $user->getPasswordHash()->value(),
                    plainPassword: $data->getPassword()
                )
            ) {
                throw new InvalidCredentialsException();
            }

            return new TokenResponseData(
                accessTokenData: $this->getAccessTokenData($user),
                refreshTokenData: $this->getRefreshTokenData($user),
            );
        } catch (InvalidUserAccountEmailException|CreateRefreshTokenException|TokenGenerateException $e) {
            throw new InvalidCredentialsException('Failed to process user credentials', previous: $e);
        }
    }

    /**
     * @throws TokenGenerateException
     */
    private function getAccessTokenData(UserAccount $user): AccessTokenData
    {
        $grantResult = new GrantResultData(
            subjectUlid: $user->getUlid()->value(),
            subjectType: $this->accountType,
            roles: $user->getRoles()->toStrings(),
            scopes: [],
        );

        return $this->tokenGenerator->generateAccessToken($grantResult);
    }

    /**
     * @throws CreateRefreshTokenException
     */
    private function getRefreshTokenData(UserAccount $user): RefreshTokenData
    {
        return $this->refreshTokenService->create($user->getUlid()->value(), $this->accountType);
    }
}
