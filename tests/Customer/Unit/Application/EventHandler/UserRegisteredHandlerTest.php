<?php

declare(strict_types=1);

namespace App\Tests\Customer\Unit\Application\EventHandler;

use App\Customer\Application\Command\CreateCustomerProfile\CreateCustomerProfileCommand;
use App\Customer\Application\EventHandler\UserRegisteredHandler;
use App\Customer\Domain\Repository\CustomerProfileReadRepositoryInterface;
use App\Shared\Domain\Event\UserRegisteredSharedEvent;
use App\Tests\Customer\Support\CustomerProfileMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class UserRegisteredHandlerTest extends BaseUnitTest
{
    private CustomerProfileReadRepositoryInterface&MockObject $readRepository;
    private LoggerInterface&MockObject $logger;
    private MessageBusInterface&MockObject $commandBus;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(CustomerProfileReadRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->commandBus = $this->createMock(MessageBusInterface::class);
    }

    public function testItDispatchesCommand(): void
    {
        $event = new UserRegisteredSharedEvent(
            id: CustomerProfileMother::DEFAULT_USER_ULID,
            email: 'user@example.com',
        );

        $this->readRepository->expects(self::once())->method('existsByUlid')->willReturn(false);
        $this->commandBus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(fn (CreateCustomerProfileCommand $command) => $command->userUlid === $event->id))
            ->willReturn(new Envelope(new stdClass()));

        $this->createHandler()($event);
    }

    public function testItSkipsDispatchingWhenCustomerProfileAlreadyExists(): void
    {
        $event = new UserRegisteredSharedEvent(
            id: CustomerProfileMother::DEFAULT_USER_ULID,
            email: 'user@example.com',
        );

        $this->readRepository->expects(self::once())->method('existsByUlid')->willReturn(true);
        $this->logger->expects(self::once())
            ->method('info')
            ->with(self::equalTo('Registered user already have customer profile'));

        $this->createHandler()($event);
    }

    private function createHandler(): UserRegisteredHandler
    {
        return new UserRegisteredHandler(
            readRepository: $this->readRepository,
            logger: $this->logger,
            commandBus: $this->commandBus
        );
    }
}
