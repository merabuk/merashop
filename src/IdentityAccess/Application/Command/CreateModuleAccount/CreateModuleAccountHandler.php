<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Command\CreateModuleAccount;

use App\IdentityAccess\Application\Exceptions\ModuleAccount\CreateModuleAccountException;
use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Exception\PasswordGenerateException;
use App\IdentityAccess\Domain\Repository\ModuleAccountWriteRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordGenerator;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientSecretHash;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\ScopeCollection;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class CreateModuleAccountHandler implements CommandHandlerInterface
{
    public function __construct(
        private PasswordGenerator $passwordGenerator,
        private ModuleAccountWriteRepositoryInterface $writeRepository,
        private PasswordHasherInterface $passwordHasher,
        private UlidGeneratorInterface $ulidGenerator,
    ) {
    }

    /**
     * @throws CreateModuleAccountException
     */
    public function __invoke(CreateModuleAccountCommand $command): string
    {
        try {
            $plainSecret = $this->passwordGenerator->generateClientSecret();
            $secretHash = $this->passwordHasher->hash($plainSecret);
            $ulid = $this->ulidGenerator->next();

            $module = ModuleAccount::create(
                ulid: Ulid::fromString($ulid),
                clientId: ClientId::fromString($command->clientId),
                clientSecret: ClientSecretHash::fromString($secretHash),
                scopes: ScopeCollection::fromStrings($command->scopes),
            );

            $this->writeRepository->save($module);

            return $plainSecret;
        } catch (PasswordGenerateException|InvalidIdentityAccessValueObjectException $e) {
            throw new CreateModuleAccountException(message: 'Error during creating module account', previous: $e);
        }
    }
}
