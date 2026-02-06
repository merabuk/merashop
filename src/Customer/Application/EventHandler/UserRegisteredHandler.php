<?php

declare(strict_types=1);

namespace App\Customer\Application\EventHandler;

use App\Customer\Application\Command\CreateCustomerProfile\CreateCustomerProfileCommand;
use App\Customer\Domain\Repository\CustomerProfileReadRepositoryInterface;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Bus\TransportNameEnum;
use App\Shared\Domain\Event\EventHandlerInterface;
use App\Shared\Domain\Event\UserRegisteredSharedEvent;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler(bus: BusNameEnum::Event->value, fromTransport: TransportNameEnum::CustomerExternal->value)]
class UserRegisteredHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly CustomerProfileReadRepositoryInterface $readRepository,
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $commandBus,
    ) {
    }

    /**
     * @throws InvalidUlidException
     * @throws ExceptionInterface
     */
    public function __invoke(UserRegisteredSharedEvent $event): void
    {
        if ($this->readRepository->existsByUlid(Ulid::fromString($event->id))) {
            $this->logger->info('Registered user already have customer profile');

            return;
        }

        $command = new CreateCustomerProfileCommand($event->id);
        $this->commandBus->dispatch($command);
    }
}
