<?php

declare(strict_types=1);

namespace App\EmailSender\Presentation\Console;

use App\EmailSender\Application\Service\OutboxEmailRelayService;
use App\Shared\Presentation\Console\BaseConsoleCommand;
use DateMalformedStringException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:email-sender:outbox-relay',
    description: 'Finds pending outbox emails and dispatches sending commands'
)]
final class OutboxRelayCommand extends BaseConsoleCommand
{
    use LockableTrait;

    public function __construct(
        ValidatorInterface $validator,
        private readonly OutboxEmailRelayService $relayService,
    ) {
        parent::__construct(validator: $validator);
    }

    /**
     * @throws ExceptionInterface
     * @throws DateMalformedStringException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $processedCount = $this->relayService->execute();

        match ($processedCount) {
            0 => $this->io->success('No pending emails found'),
            default => $this->io->success(sprintf('Dispatched %d emails for processing', $processedCount)),
        };

        return Command::SUCCESS;
    }
}
