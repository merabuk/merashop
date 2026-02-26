<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Command\CreateModuleAccount;

use App\IdentityAccess\Application\Command\CreateModuleAccount\CreateModuleAccountCommand;
use App\IdentityAccess\Application\Command\CreateModuleAccount\CreateModuleAccountHandler;
use App\IdentityAccess\Domain\Exception\ModuleAccount\ModuleAccountAlreadyExistsException;
use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\ModuleAccountWriteRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordGeneratorInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use App\Tests\IdentityAccess\Support\ModuleAccountMother;
use PHPUnit\Framework\TestCase;

class CreateModuleAccountHandlerTest extends TestCase
{
    private ModuleAccountReadRepositoryInterface $readRepository;
    private PasswordGeneratorInterface $passwordGenerator;
    private ModuleAccountWriteRepositoryInterface $writeRepository;
    private PasswordHasherInterface $passwordHasher;
    private UlidGeneratorInterface $ulidGenerator;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(ModuleAccountReadRepositoryInterface::class);
        $this->passwordGenerator = $this->createMock(PasswordGeneratorInterface::class);
        $this->writeRepository = $this->createMock(ModuleAccountWriteRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->ulidGenerator = $this->createMock(UlidGeneratorInterface::class);
    }

    public function testItSuccessfullyCreateModuleAccount(): void
    {
        $clientId = ModuleAccountMother::DEFAULT_CLIENT_ID;
        $command = new CreateModuleAccountCommand(
            clientId: $clientId,
            scopes: []
        );

        $this->readRepository->method('existsByClientId')->willReturn(false);
        $this->passwordGenerator->method('generateClientSecret')->willReturn('plain_secret');
        $this->passwordHasher->method('hash')->willReturn('hashed_secret');
        $this->ulidGenerator->method('next')->willReturn(ModuleAccountMother::DEFAULT_ULID);

        $this->writeRepository->expects(self::once())
            ->method('save')
            ->willReturn(ModuleAccountMother::createWithData(clientId: $clientId));

        $result = $this->createHandler()($command);

        self::assertSame('plain_secret', $result);
    }

    public function testThrowsExceptionWhenModuleAccountAlreadyExists(): void
    {
        $clientId = ModuleAccountMother::DEFAULT_CLIENT_ID;
        $command = new CreateModuleAccountCommand(
            clientId: $clientId,
            scopes: []
        );

        $this->readRepository->method('existsByClientId')->willReturn(true);

        $this->expectException(ModuleAccountAlreadyExistsException::class);
        $this->createHandler()($command);
    }

    private function createHandler(): CreateModuleAccountHandler
    {
        return new CreateModuleAccountHandler(
            readRepository: $this->readRepository,
            passwordGenerator: $this->passwordGenerator,
            writeRepository: $this->writeRepository,
            passwordHasher: $this->passwordHasher,
            ulidGenerator: $this->ulidGenerator
        );
    }
}
