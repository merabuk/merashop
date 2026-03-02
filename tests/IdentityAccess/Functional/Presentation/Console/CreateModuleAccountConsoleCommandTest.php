<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Functional\Presentation\Console;

use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Presentation\Console\CreateModuleAccountConsoleCommand;
use App\Shared\Domain\Enum\ScopeEnum;
use App\Tests\IdentityAccess\Support\Traits\ModuleAccountFactoryTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateModuleAccountConsoleCommandTest extends KernelTestCase
{
    use ModuleAccountFactoryTrait;

    private CommandTester $commandTester;
    private ModuleAccountReadRepositoryInterface $readRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find(CreateModuleAccountConsoleCommand::COMMAND_NAME);
        $this->commandTester = new CommandTester($command);

        $this->readRepository = self::getContainer()->get(ModuleAccountReadRepositoryInterface::class);
    }

    public function testItCreatesModuleAccountSuccessfullyWithInteractiveInput(): void
    {
        $clientId = 'new-client-id';
        $scope = ScopeEnum::UserRead;

        $this->commandTester->setInputs([
            $clientId,
            implode(',', [$scope->value]),
        ]);

        $this->commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Module account created!', $output);
        self::assertStringContainsString('Secret:', $output);

        $module = $this->readRepository->findByClientId(ClientId::fromString($clientId));
        self::assertNotNull($module);
        self::assertTrue($module->getScopes()->contains($scope->value));
    }

    public function testItValidationLoopWorksOnInvalidData(): void
    {
        $invalidClientId = 'id';
        $validClientId = 'fixed-client-id';

        $this->commandTester->setInputs([
            $invalidClientId,
            $validClientId,
            '0',
        ]);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();

        self::assertStringContainsString('This value is too short', $output);
        self::assertStringContainsString('Module account created!', $output);

        $module = $this->readRepository->findByClientId(ClientId::fromString($validClientId));
        self::assertNotNull($module);
    }

    public function testItFailsWhenClientIdAlreadyExists(): void
    {
        $existingModule = $this->getModuleAccountFixture()->create(clientId: 'existing-client-id');
        $clientIdValue = $existingModule->getClientId()->value();

        $this->commandTester->setInputs([
            $clientIdValue,
            'exists-again-client-id',
            '0',
        ]);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();

        self::assertStringContainsString('This Client ID is already in use', $output);
        self::assertStringContainsString('Module account created!', $output);
    }

    public function testItSupportsNonInteractiveArguments(): void
    {
        $clientId = 'interactive-client-id';

        $this->commandTester->execute([
            'clientId' => $clientId,
            '--scope' => [ScopeEnum::UserRead->value],
        ]);

        self::assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        $module = $this->readRepository->findByClientId(ClientId::fromString($clientId));
        self::assertNotNull($module);
    }

    public function testItWorksInNonInteractiveMode(): void
    {
        $clientId = 'non-interactive-client-id';

        $this->commandTester->execute([
            'clientId' => $clientId,
            '--scope' => [ScopeEnum::UserRead->value],
        ], [
            'interactive' => false,
        ]);

        self::assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Module account created!', $output);

        $module = $this->readRepository->findByClientId(ClientId::fromString($clientId));
        self::assertNotNull($module);
    }

    #[DataProvider('invalidInputProvider')]
    public function testThrowsExceptionOnInvalidInputInNonInteractiveMode(
        string $clientId,
        array $scopes,
        string $expectedErrorMessage,
    ): void {
        $this->commandTester->execute([
            'clientId' => $clientId,
            '--scope' => $scopes,
        ], [
            'interactive' => false,
        ]);

        self::assertSame(Command::FAILURE, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString($expectedErrorMessage, $output);
    }

    public static function invalidInputProvider(): iterable
    {
        yield 'invalid client id' => [
            'id',
            [ScopeEnum::UserRead->value],
            'Client ID must be at least',
        ];
        yield 'invalid scope' => [
            'module-client-id',
            ['invalid-scope-1', 'invalid-scope-2'],
            'Invalid scopes: "invalid-scope-1", "invalid-scope-2"',
        ];
    }
}
