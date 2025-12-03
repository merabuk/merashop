<?php

declare(strict_types=1);

namespace App\Users\Application\Service;

use App\Users\Application\Dto\UserRegisterDto;
use App\Users\Domain\Entity\User;
use App\Users\Domain\Event\UserRegisteredEvent;
use App\Users\Domain\Repository\UserRepositoryInterface;
use App\Users\Domain\Service\PasswordHasherInterface;

readonly class UserRegisterService
{
    public function __construct(
        private PasswordHasherInterface $passwordHasher,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function process(UserRegisterDto $dto): UserRegisteredEvent
    {
        $passwordHash = $this->passwordHasher->hash($dto->password);

        $user = new User();

        $user->setEmail($dto->email);
        $user->setPhoneNumber($dto->phoneNumber);
        $user->setPassword($passwordHash);

        $this->userRepository->save($user);

        return new UserRegisteredEvent($user);
    }
}
