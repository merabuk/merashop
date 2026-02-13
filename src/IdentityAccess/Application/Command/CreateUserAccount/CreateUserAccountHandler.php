<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Command\CreateUserAccount;

use App\IdentityAccess\Application\Exceptions\UserAccount\CreateUserAccountException;
use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Exception\UserAccount\UserAccountAlreadyExistsException;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\UserAccountWriteRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\UserAccount\PasswordHash;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Ulid;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Enum\RoleEnum;
use App\Shared\Domain\Event\UserRegisteredSharedEvent;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class CreateUserAccountHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserAccountReadRepositoryInterface $readRepository,
        private UserAccountWriteRepositoryInterface $writeRepository,
        private PasswordHasherInterface $passwordHasher,
        private MessageBusInterface $eventBus,
        private UlidGeneratorInterface $ulidGenerator,
    ) {
    }

    /**
     * @throws CreateUserAccountException
     * @throws UserAccountAlreadyExistsException
     */
    public function __invoke(CreateUserAccountCommand $command): void
    {
        try {
            $email = EmailAddress::fromString($command->email);

            if ($this->readRepository->existsByEmail($email)) {
                throw new UserAccountAlreadyExistsException();
            }

            $passwordHash = $this->passwordHasher->hash($command->password);
            $ulid = $this->ulidGenerator->next();

            $user = UserAccount::create(
                ulid: Ulid::fromString($ulid),
                email: $email,
                passwordHash: PasswordHash::fromString($passwordHash),
                // TODO[user_account]: refactor roles in future
                roles: RoleCollection::fromStrings([RoleEnum::User->value, RoleEnum::Customer->value]),
            );

            $this->writeRepository->save($user);

            $this->eventBus->dispatch(new UserRegisteredSharedEvent(
                id: $user->getUlid()->value(),
                email: $user->getEmail()->value(),
            ));
        } catch (InvalidIdentityAccessValueObjectException|ExceptionInterface $e) {
            throw new CreateUserAccountException(message: 'Error during creating user account', previous: $e);
        }
    }
}
