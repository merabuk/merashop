<?php

declare(strict_types=1);

namespace App\Users\Application\Command\CreateUser;

use App\Shared\Application\Bus\BusName;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Users\Domain\Entity\User;
use App\Users\Domain\Repository\UserRepositoryInterface;
use App\Users\Domain\Service\PasswordHasherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusName::Command->value)]
readonly class CreateUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(CreateUserCommand $command): int
    {
        $passwordHash = $this->passwordHasher->hash($command->createUserDto->password);

        $user = new User();

        $user->setFirstName($command->createUserDto->firstName);
        $user->setLastName($command->createUserDto->lastName);
        $user->setEmail($command->createUserDto->email);
        $user->setPassword($passwordHash);
        $user->setPhoneNumber($command->createUserDto->phoneNumber);

        $this->userRepository->save($user);

        return $user->getId();
    }
}
