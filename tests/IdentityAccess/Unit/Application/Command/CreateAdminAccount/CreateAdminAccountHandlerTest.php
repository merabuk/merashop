<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Command\CreateAdminAccount;

use App\IdentityAccess\Application\Command\CreateAdminAccount\CreateAdminAccountCommand;
use App\IdentityAccess\Application\Command\CreateAdminAccount\CreateAdminAccountHandler;
use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;
use App\IdentityAccess\Domain\Exception\AdminAccount\AdminAccountAlreadyExistsException;
use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\AdminAccountWriteRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordGeneratorInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\Shared\Domain\Enum\RoleEnum;
use App\Shared\Domain\Event\AdminCreatedSharedEvent;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use App\Tests\IdentityAccess\Support\AdminAccountMother;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class CreateAdminAccountHandlerTest extends TestCase
{
    private AdminAccountReadRepositoryInterface $readRepository;
    private PasswordGeneratorInterface $passwordGenerator;
    private AdminAccountWriteRepositoryInterface $writeRepository;
    private PasswordHasherInterface $passwordHasher;
    private UlidGeneratorInterface $ulidGenerator;
    private MessageBusInterface $eventBus;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(AdminAccountReadRepositoryInterface::class);
        $this->passwordGenerator = $this->createMock(PasswordGeneratorInterface::class);
        $this->writeRepository = $this->createMock(AdminAccountWriteRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->ulidGenerator = $this->createMock(UlidGeneratorInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);
    }

    public function testItSuccessfullyCreatesAdmin(): void
    {
        $email = AdminAccountMother::DEFAULT_EMAIL;
        $command = new CreateAdminAccountCommand(
            email: $email,
            status: StatusEnum::Active->value,
            roles: [RoleEnum::Admin->value]
        );

        $this->readRepository->method('existsByEmail')->willReturn(false);
        $this->passwordGenerator->method('generateTemporaryAdminPassword')->willReturn('plain_password');
        $this->passwordHasher->method('hash')->willReturn('hashed_password');
        $this->ulidGenerator->method('next')->willReturn(AdminAccountMother::DEFAULT_ULID);

        $this->writeRepository->expects(self::once())
            ->method('save')
            ->willReturn(AdminAccountMother::createWithData(email: $email));

        $this->eventBus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(AdminCreatedSharedEvent::class))
            ->willReturn(new Envelope(new stdClass()));

        $result = $this->createHandler()($command);

        self::assertSame('plain_password', $result);
    }

    public function testThrowsExceptionWhenAdminAlreadyExists(): void
    {
        $command = new CreateAdminAccountCommand(
            email: AdminAccountMother::DEFAULT_EMAIL,
            status: StatusEnum::Active->value,
            roles: [RoleEnum::Admin->value]
        );

        $this->readRepository->method('existsByEmail')->willReturn(true);

        $this->expectException(AdminAccountAlreadyExistsException::class);
        $this->createHandler()($command);
    }

    private function createHandler(): CreateAdminAccountHandler
    {
        return new CreateAdminAccountHandler(
            readRepository: $this->readRepository,
            passwordGenerator: $this->passwordGenerator,
            writeRepository: $this->writeRepository,
            passwordHasher: $this->passwordHasher,
            ulidGenerator: $this->ulidGenerator,
            eventBus: $this->eventBus
        );
    }
}
