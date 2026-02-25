<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Command\CreateAdminAccount;

use App\IdentityAccess\Application\Exception\AdminAccount\CreateAdminAccountException;
use App\IdentityAccess\Domain\Entity\AdminAccount;
use App\IdentityAccess\Domain\Exception\AdminAccount\AdminAccountAlreadyExistsException;
use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\AdminAccountWriteRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordGeneratorInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordHash;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Status;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Event\AdminCreatedSharedEvent;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class CreateAdminAccountHandler implements CommandHandlerInterface
{
    public function __construct(
        private PasswordGeneratorInterface $passwordGenerator,
        private AdminAccountReadRepositoryInterface $readRepository,
        private AdminAccountWriteRepositoryInterface $writeRepository,
        private PasswordHasherInterface $passwordHasher,
        private UlidGeneratorInterface $ulidGenerator,
        private MessageBusInterface $eventBus,
    ) {
    }

    /**
     * @throws CreateAdminAccountException
     * @throws AdminAccountAlreadyExistsException
     */
    public function __invoke(CreateAdminAccountCommand $command): string
    {
        try {
            $email = EmailAddress::fromString($command->email);

            if ($this->readRepository->existsByEmail($email)) {
                throw new AdminAccountAlreadyExistsException();
            }

            $plainPassword = $this->passwordGenerator->generateTemporaryAdminPassword();
            $passwordHash = $this->passwordHasher->hash($plainPassword);
            $ulid = $this->ulidGenerator->next();

            $admin = AdminAccount::create(
                ulid: Ulid::fromString($ulid),
                email: $email,
                passwordHash: PasswordHash::fromString($passwordHash),
                roles: RoleCollection::fromStrings($command->roles),
                status: Status::fromString($command->status),
            );

            $admin = $this->writeRepository->save($admin);

            $this->eventBus->dispatch(new AdminCreatedSharedEvent(
                id: $admin->getUlid()->value(),
                email: $admin->getEmail()->value(),
                temporaryPassword: $plainPassword,
            ));

            return $plainPassword;
        } catch (AdminAccountAlreadyExistsException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new CreateAdminAccountException(message: 'Error during creating admin account', previous: $e);
        }
    }
}
