<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Command\CreateUserAccount;

use App\IdentityAccess\Application\Command\CreateUserAccount\CreateUserAccountCommand;
use App\IdentityAccess\Application\Command\CreateUserAccount\CreateUserAccountHandler;
use App\IdentityAccess\Domain\Exception\UserAccount\UserAccountAlreadyExistsException;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\UserAccountWriteRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\Shared\Domain\Event\UserRegisteredSharedEvent;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateUserAccountHandlerTest extends TestCase
{
    private UserAccountReadRepositoryInterface $readRepository;
    private PasswordHasherInterface $passwordHasher;
    private UlidGeneratorInterface $ulidGenerator;
    private UserAccountWriteRepositoryInterface $writeRepository;
    private MessageBusInterface $eventBus;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(UserAccountReadRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->ulidGenerator = $this->createMock(UlidGeneratorInterface::class);
        $this->writeRepository = $this->createMock(UserAccountWriteRepositoryInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);
    }

    public function testItSuccessfullyCreatesUserAccount(): void
    {
        $email = UserAccountMother::DEFAULT_EMAIL;
        $command = new CreateUserAccountCommand(
            email: $email,
            password: 'password',
        );

        $this->readRepository->method('existsByEmail')->willReturn(false);
        $this->passwordHasher->method('hash')->willReturn('hashed_password');
        $this->ulidGenerator->method('next')->willReturn(UserAccountMother::DEFAULT_ULID);

        $this->writeRepository->expects(self::once())
            ->method('save')
            ->willReturn(UserAccountMother::createWithData(email: $email));

        $this->eventBus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(UserRegisteredSharedEvent::class))
            ->willReturn(new Envelope(new stdClass()));

        $this->createHandler()($command);
    }

    public function testThrowsExceptionWhenUserAlreadyExists(): void
    {
        $email = UserAccountMother::DEFAULT_EMAIL;
        $command = new CreateUserAccountCommand(
            email: $email,
            password: 'password',
        );

        $this->readRepository->method('existsByEmail')->willReturn(true);

        $this->expectException(UserAccountAlreadyExistsException::class);
        $this->createHandler()($command);
    }

    private function createHandler(): CreateUserAccountHandler
    {
        return new CreateUserAccountHandler(
            readRepository: $this->readRepository,
            passwordHasher: $this->passwordHasher,
            ulidGenerator: $this->ulidGenerator,
            writeRepository: $this->writeRepository,
            eventBus: $this->eventBus
        );
    }
}
