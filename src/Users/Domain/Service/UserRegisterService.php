<?php

declare(strict_types=1);

namespace App\Users\Domain\Service;

use App\Users\Domain\Exception\UserAlreadyExistsException;
use App\Users\Domain\Repository\UserReadRepositoryInterface;
use App\Users\Domain\ValueObject\EmailAddress;

readonly class UserRegisterService
{
    public function __construct(
        private UserReadRepositoryInterface $userReadRepository,
    ) {
    }

    /**
     * @throws UserAlreadyExistsException
     */
    public function canRegister(EmailAddress $email): void
    {
        if ($this->userReadRepository->findByEmail($email)) {
            throw new UserAlreadyExistsException();
        }
    }
}
