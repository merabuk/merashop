<?php

declare(strict_types=1);

namespace App\Users\Application\Command\CreateUser;

use App\Shared\Application\Bus\BusName;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Event\UserRegisteredEvent;
use App\Users\Domain\Entity\User;
use App\Users\Domain\Exception\InvalidUserValueObjectException;
use App\Users\Domain\Exception\UserAlreadyExistsException;
use App\Users\Domain\Repository\UserWriteRepositoryInterface;
use App\Users\Domain\Service\PasswordHasherInterface;
use App\Users\Domain\Service\UserRegisterService;
use App\Users\Domain\ValueObject\EmailAddress;
use App\Users\Domain\ValueObject\FirstName;
use App\Users\Domain\ValueObject\LastName;
use App\Users\Domain\ValueObject\PasswordHash;
use App\Users\Domain\ValueObject\PhoneNumber;
use App\Users\Domain\ValueObject\Ulid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler(bus: BusName::Command->value)]
readonly class CreateUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRegisterService $userRegisterService,
        private PasswordHasherInterface $passwordHasher,
        private UserWriteRepositoryInterface $userWriteRepository,
        private MessageBusInterface $eventBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidUserValueObjectException
     * @throws UserAlreadyExistsException
     */
    public function __invoke(CreateUserCommand $command): int
    {
        $email = EmailAddress::fromString($command->email);

        $this->userRegisterService->canRegister($email);

        $passwordHashString = $this->passwordHasher->hash($command->password);

        $user = new User(
            id: null,
            ulid: Ulid::generate(),
            email: $email,
            firstName: FirstName::fromString($command->firstName),
            lastName: LastName::fromString($command->lastName),
            phoneNumber: $command->phoneNumber ? PhoneNumber::fromString($command->phoneNumber) : null,
            password: PasswordHash::fromString($passwordHashString),
        );

        $this->userWriteRepository->save($user);

        $userId = $user->getId();

        $event = new UserRegisteredEvent(
            id: $user->getUlid()->value(),
            email: $user->getEmail()->value(),
            name: $user->getFirstName()->value(),
        );

        $this->eventBus->dispatch($event);

        return $userId?->value();
    }
}
