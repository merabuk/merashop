<?php

declare(strict_types=1);

namespace App\EmailSender\Application\Service;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use DateMalformedStringException;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class OutboxEmailRelayService
{
    public function __construct(
        private OutboxEmailReadRepositoryInterface $readRepository,
        private MessageBusInterface $commandBus,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     * @throws ExceptionInterface
     */
    public function execute(): int
    {
        $limit = 100;
        $now = $this->clock->now();
        $staleTime = $now->modify('-10 minutes');

        $emails = $this->readRepository->findReadyToProcess($limit, $now, $staleTime);

        foreach ($emails as $email) {
            $this->commandBus->dispatch(new SendOutboxEmailCommand($email->getId()->value()));
        }

        return count($emails);
    }
}
