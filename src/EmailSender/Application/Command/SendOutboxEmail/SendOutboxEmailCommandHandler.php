<?php

declare(strict_types=1);

namespace App\EmailSender\Application\Command\SendOutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailAttemptsException;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailErrorMessageException;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\EmailSender\Domain\Service\MailerServiceInterface;
use App\EmailSender\Domain\Service\OutboxRetryPolicy;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class SendOutboxEmailCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private OutboxEmailReadRepositoryInterface $outboxEmailReadRepository,
        private OutboxEmailWriteRepositoryInterface $outboxEmailWriteRepository,
        private MailerServiceInterface $mailer,
        private LoggerInterface $logger,
        private OutboxRetryPolicy $retryPolicy,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     * @throws InvalidOutboxEmailAttemptsException
     * @throws InvalidOutboxEmailErrorMessageException
     */
    public function __invoke(SendOutboxEmailCommand $command): void
    {
        $email = $this->outboxEmailReadRepository->findById($command->id);

        if (!$email || !$email->canBeProcessed()) {
            $this->logger->notice("Email not found or can't be processed", [
                'id' => $command->id,
                'trace_id' => $email->getTraceId()?->value(),
                'found' => (bool) $email,
            ]);

            return;
        }

        try {
            $email->lock($this->clock->now());
            $email = $this->outboxEmailWriteRepository->save($email);

            $this->mailer->process($email);

            $email->markAsSent();
        } catch (\Throwable $e) {
            if ($this->retryPolicy->shouldRetry($email->getAttempts())) {
                $nextAttemptAt = $this->retryPolicy->calculateNextAttemptAt(
                    attempts: $email->getAttempts(),
                    now: $this->clock->now()
                );
                $email->markAsFailed(error: $e->getMessage(), nextAttemptAt: $nextAttemptAt);
            } else {
                $this->logger->critical('Email sending failed permanently', [
                    'id' => $email->getId()->value(),
                    'trace_id' => $email->getTraceId()?->value(),
                    'error' => $e->getMessage(),
                ]);

                $email->markAsFailedPermanently(error: $e->getMessage());
            }
        } finally {
            $this->outboxEmailWriteRepository->save($email);
        }
    }
}
