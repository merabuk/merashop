<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Functional\Presentation\Console;

use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;
use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\IdentityAccess\Presentation\Console\CreateAdminAccountConsoleCommand;
use App\Shared\Domain\Enum\RoleEnum;
use App\Tests\IdentityAccess\Support\Traits\AdminAccountFactoryTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateAdminAccountConsoleCommandTest extends KernelTestCase
{
    use AdminAccountFactoryTrait;

    private CommandTester $commandTester;
    private AdminAccountReadRepositoryInterface $readRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find(CreateAdminAccountConsoleCommand::COMMAND_NAME);
        $this->commandTester = new CommandTester($command);

        $this->readRepository = self::getContainer()->get(AdminAccountReadRepositoryInterface::class);
    }

    public function testItCreatesAdminAccountSuccessfullyWithInteractiveInput(): void
    {
        $email = 'new.admin@example.com';
        $status = StatusEnum::Active;
        $role = RoleEnum::Admin;

        $this->commandTester->setInputs([
            $email,
            $status->value,
            implode(',', [$role->value]),
        ]);

        $this->commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Admin account created!', $output);
        self::assertStringContainsString('Temporary password:', $output);

        $admin = $this->readRepository->findByEmail(EmailAddress::fromString($email));
        self::assertNotNull($admin);
        self::assertSame($status, $admin->getStatus()->value());
        self::assertTrue($admin->getRoles()->contains($role->value));
    }

    public function testItValidationLoopWorksOnInvalidData(): void
    {
        $invalidEmail = 'not-an-email';
        $validEmail = 'fixed@example.com';

        $this->commandTester->setInputs([
            $invalidEmail,
            $validEmail,
            StatusEnum::Active->value,
            '0',
        ]);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();

        self::assertStringContainsString('This value is not a valid email address', $output);
        self::assertStringContainsString('Admin account created!', $output);

        $admin = $this->readRepository->findByEmail(EmailAddress::fromString($validEmail));
        self::assertNotNull($admin);
    }

    public function testItFailsWhenEmailAlreadyExists(): void
    {
        $existingAdmin = $this->getAdminAccountFixture()->create(email: 'exists@example.com');
        $emailValue = $existingAdmin->getEmail()->value();

        $this->commandTester->setInputs([
            $emailValue,
            'exists-again@example.com',
            StatusEnum::Active->value,
            '0',
        ]);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();

        self::assertStringContainsString('This email is already in use', $output);
        self::assertStringContainsString('Admin account created!', $output);
    }

    public function testItSupportsNonInteractiveArguments(): void
    {
        $email = 'direct@example.com';

        $this->commandTester->execute([
            'email' => $email,
            'status' => StatusEnum::Active->value,
            '--role' => [RoleEnum::Admin->value],
        ]);

        self::assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        $admin = $this->readRepository->findByEmail(EmailAddress::fromString($email));
        self::assertNotNull($admin);
    }

    public function testItWorksInNonInteractiveMode(): void
    {
        $email = 'non-interactive@example.com';

        $this->commandTester->execute([
            'email' => $email,
            'status' => StatusEnum::Active->value,
            '--role' => [RoleEnum::Admin->value],
        ], [
            'interactive' => false,
        ]);

        self::assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Admin account created!', $output);

        $admin = $this->readRepository->findByEmail(EmailAddress::fromString($email));
        self::assertNotNull($admin);
    }

    #[DataProvider('invalidInputProvider')]
    public function testThrowsExceptionOnInvalidInputInNonInteractiveMode(
        string $email,
        string $status,
        array $roles,
        string $expectedErrorMessage,
    ): void {
        $this->commandTester->execute([
            'email' => $email,
            'status' => $status,
            '--role' => $roles,
        ], [
            'interactive' => false,
        ]);

        self::assertSame(Command::FAILURE, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString($expectedErrorMessage, $output);
    }

    public static function invalidInputProvider(): iterable
    {
        yield 'invalid email' => [
            'invalid-email',
            StatusEnum::Active->value,
            [RoleEnum::Admin->value],
            'Invalid email format: invalid-email',
        ];
        yield 'invalid status' => [
            'admin@example.com',
            'invalid-status',
            [RoleEnum::Admin->value],
            'Invalid status "invalid-status"',
        ];
        yield 'invalid role' => [
            'admin@example.com',
            StatusEnum::Active->value,
            ['invalid-role-1', 'invalid-role-2'],
            'Invalid roles: "invalid-role-1", "invalid-role-2"',
        ];
    }
}
