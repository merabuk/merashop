<?php

declare(strict_types=1);

namespace App\Users\Application\Command\CreateUser;

use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Infrastructure\Bus\BusName;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusName::Command->value)]
class CreateUserCommandHandler implements CommandHandlerInterface
{
    public function __invoke(CreateUserCommand $command): int
    {
        // TODO: Implement __invoke() method.
    }
}
