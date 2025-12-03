<?php

declare(strict_types=1);

namespace App\Users\Application\Command\CreateUser;

use App\Shared\Application\Command\CommandInterface;
use App\Users\Application\Dto\CreateUserDto;

readonly class CreateUserCommand implements CommandInterface
{
    public function __construct(
        public CreateUserDto $createUserDto,
    ) {
    }
}
