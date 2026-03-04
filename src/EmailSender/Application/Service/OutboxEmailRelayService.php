<?php

declare(strict_types=1);

namespace App\EmailSender\Application\Service;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use DateMalformedStringException;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class OutboxEmailRelayService implements OutboxEmailRelayServiceInterface
{
    public function __construct(
        private OutboxEmailReadRepositoryInterface $readRepository,
        private MessageBusInterface $commandBus,
        private ClockInterface $clock,
        private int $subMinutes,
        private int $limit,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     * @throws ExceptionInterface
     */
    public function execute(): int
    {
        $now = $this->clock->now();
        $staleTime = $now->modify("-{$this->subMinutes} minutes");

        $emails = $this->readRepository->findReadyToProcess($this->limit, $now, $staleTime);

        foreach ($emails as $email) {
            $this->commandBus->dispatch(new SendOutboxEmailCommand($email->getId()->value()));
        }

        return count($emails);
    }
}
