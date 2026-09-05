<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Provider\ClientCredentialsGrant;

use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\Exception\InvalidClientException;
use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountClientIdException;
use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Helpers\TypeCaster;

final readonly class ModuleClientCredentialsGrantAccountProvider implements ClientCredentialsGrantAccountProviderInterface
{
    public function __construct(
        private ModuleAccountReadRepositoryInterface $readRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getDefaultIndexName(): string
    {
        return self::getAccountType()->value;
    }

    /**
     * @throws InvalidClientException
     */
    public function handle(string $clientId, string $clientSecret): GrantResultData
    {
        try {
            $module = $this->readRepository->findByClientId(ClientId::fromString($clientId));

            if (
                null === $module
                || !$this->passwordHasher->verify(
                    hashedPassword: $module->getClientSecret()->value(),
                    plainPassword: $clientSecret
                )
            ) {
                throw new InvalidClientException('Invalid client credentials');
            }

            return new GrantResultData(
                subjectUlid: TypeCaster::castToNonEmptyString(
                    string: $module->getUlid()->value(),
                    message: 'Giving module ulid is empty'
                ),
                subjectType: self::getAccountType(),
                roles: [],
                scopes: $module->getScopes()->toStrings(),
            );
        } catch (InvalidModuleAccountClientIdException $e) {
            throw new InvalidClientException('Failed to process module credentials', previous: $e);
        }
    }

    private static function getAccountType(): IdentityTypeEnum
    {
        return IdentityTypeEnum::Module;
    }
}
