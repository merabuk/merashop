<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Functional\Presentation\Console;

use App\EmailSender\Presentation\Console\OutboxRelayCommand;
use App\Tests\EmailSender\Support\Traits\OutboxEmailFactoryTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class OutboxRelayCommandTest extends KernelTestCase
{
    use OutboxEmailFactoryTrait;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find(OutboxRelayCommand::COMMAND_NAME);
        $this->commandTester = new CommandTester($command);
    }

    public function testItExecutesRelayCommandAndAlreadyProcessRecords(): void
    {
        $processedCount = 3;

        for ($i = 0; $i < $processedCount; ++$i) {
            $this->getOutboxEmailFixture()->createCreatedEmail();
        }
        $this->commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString(sprintf('Dispatched %d emails for processing', $processedCount), $output);
    }

    public function testItExecutesRelayCommandAndNoAlreadyProcessRecords(): void
    {
        $this->commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('No pending emails found', $output);
    }
}
