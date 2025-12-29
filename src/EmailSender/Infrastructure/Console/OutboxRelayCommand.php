<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Console;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:email-sender:outbox-relay',
    description: 'Finds pending outbox emails and dispatches sending commands'
)]
final class OutboxRelayCommand extends Command
{
    use LockableTrait;

    public function __construct(
        private readonly OutboxEmailReadRepositoryInterface $readRepository,
        private readonly MessageBusInterface $commandBus,
    ) {
        parent::__construct();
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $emails = $this->readRepository->findReadyToProcess(100);

        if (empty($emails)) {
            $io->success('No pending emails found');

            return Command::SUCCESS;
        }

        foreach ($emails as $email) {
            $this->commandBus->dispatch(new SendOutboxEmailCommand($email->getId()->value()));
        }

        $io->success(sprintf('Dispatched %d emails for processing', count($emails)));

        return Command::SUCCESS;
    }
}
