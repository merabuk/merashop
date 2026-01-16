<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\DTO\RefreshTokenData;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\DTO\UserCredentialsInterface;
use App\IdentityAccess\Application\Exceptions\BadCredentialsException;
use App\IdentityAccess\Application\Exceptions\CreateRefreshTokenException;
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
     * @throws BadCredentialsException
     */
    public function handle(UserCredentialsInterface $data): TokenResponseData
    {
        try {
            $user = $this->userAccountReadRepository->findByEmail(
                EmailAddress::fromString($data->getUsername())
            );

            if (null === $user || !$this->passwordHasher->verify($user->getPasswordHash()->value(), $data->getPassword())) {
                throw BadCredentialsException::becauseInvalidCredentials();
            }

            return new TokenResponseData(
                accessTokenData: $this->getAccessTokenData($user),
                refreshTokenData: $this->getRefreshTokenData($user),
            );
        } catch (InvalidUserAccountEmailException|CreateRefreshTokenException $e) {
            throw BadCredentialsException::becauseInvalidCredentials(previous: $e);
        }
    }

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
