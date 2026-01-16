<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\ClientCredentialsInterface;
use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\Exceptions\BadCredentialsException;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\Enum\AccountTypeEnum;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountClientIdException;
use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;

readonly class ClientCredentialsGrantHandler implements GrantHandlerInterface
{
    private AccountTypeEnum $accountType;

    public function __construct(
        private ModuleAccountReadRepositoryInterface $moduleAccountReadRepository,
        private PasswordHasherInterface $passwordHasher,
        private TokenGeneratorInterface $tokenGenerator,
    ) {
        $this->accountType = AccountTypeEnum::Module;
    }

    public function supports(GrantTypeEnum $grantType): bool
    {
        return GrantTypeEnum::ClientCredentials === $grantType;
    }

    /**
     * @throws BadCredentialsException
     */
    public function handle(ClientCredentialsInterface $data): TokenResponseData
    {
        try {
            $module = $this->moduleAccountReadRepository->findByClientId(
                ClientId::fromString($data->getClientId())
            );

            if (null === $module || !$this->passwordHasher->verify($module->getClientSecret()->value(), $data->getClientSecret())) {
                throw BadCredentialsException::becauseInvalidCredentials();
            }

            return new TokenResponseData(accessTokenData: $this->getAccessTokenData($module));
        } catch (InvalidModuleAccountClientIdException $e) {
            throw BadCredentialsException::becauseInvalidCredentials(previous: $e);
        }
    }

    private function getAccessTokenData(ModuleAccount $module): AccessTokenData
    {
        $grantResult = new GrantResultData(
            subjectUlid: $module->getUlid()->value(),
            subjectType: $this->accountType,
            roles: [],
            scopes: $module->getScopes()->toStrings(),
        );

        return $this->tokenGenerator->generateAccessToken($grantResult);
    }
}
