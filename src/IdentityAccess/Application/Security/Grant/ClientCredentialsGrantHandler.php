<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\ClientCredentialsInterface;
use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\Exception\GrantHandlerException;
use App\IdentityAccess\Application\Exception\InvalidClientException;
use App\IdentityAccess\Application\Exception\TokenGenerateException;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountClientIdException;
use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\Shared\Domain\Enum\IdentityTypeEnum;

readonly class ClientCredentialsGrantHandler implements GrantHandlerInterface
{
    private IdentityTypeEnum $accountType;

    public function __construct(
        private ModuleAccountReadRepositoryInterface $moduleAccountReadRepository,
        private PasswordHasherInterface $passwordHasher,
        private TokenGeneratorInterface $tokenGenerator,
    ) {
        $this->accountType = IdentityTypeEnum::Module;
    }

    public static function getDefaultIndexName(): string
    {
        return GrantTypeEnum::ClientCredentials->value;
    }

    /**
     * @throws GrantHandlerException
     */
    public function handle(ClientCredentialsInterface $data): TokenResponseData
    {
        try {
            $module = $this->moduleAccountReadRepository->findByClientId(
                ClientId::fromString($data->getClientId())
            );

            if (
                null === $module
                || !$this->passwordHasher->verify(
                    hashedPassword: $module->getClientSecret()->value(),
                    plainPassword: $data->getClientSecret()
                )
            ) {
                throw new InvalidClientException('Invalid client credentials');
            }

            return new TokenResponseData(accessTokenData: $this->getAccessTokenData($module));
        } catch (InvalidModuleAccountClientIdException|TokenGenerateException $e) {
            throw new InvalidClientException('Failed to process client credentials', previous: $e);
        }
    }

    /**
     * @throws TokenGenerateException
     */
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
