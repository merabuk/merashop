<?php

declare(strict_types=1);

namespace App\EmailSender\Application\Command\SendOutboxEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\EmailSender\Domain\Service\MailerServiceInterface;
use App\EmailSender\Domain\Service\OutboxRetryPolicy;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Id;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use DateMalformedStringException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class SendOutboxEmailCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private OutboxEmailReadRepositoryInterface $readRepository,
        private OutboxEmailWriteRepositoryInterface $writeRepository,
        private MailerServiceInterface $mailer,
        private LoggerInterface $logger,
        private OutboxRetryPolicy $retryPolicy,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     * @throws InvalidEmailSenderValueObjectException
     */
    public function __invoke(SendOutboxEmailCommand $command): void
    {
        $email = $this->readRepository->findByIdForUpdate(Id::fromInt($command->id));

        if (!$email || !$email->canBeProcessed($this->clock)) {
            $this->logger->notice("Email not found or can't be processed", [
                'id' => $command->id,
                'trace_id' => $email?->getTraceId()?->value(),
                'found' => (bool) $email,
            ]);

            return;
        }

        try {
            $email->lock($this->clock->now());
            $email = $this->writeRepository->save($email);

            $this->mailer->process($email);

            $email->markAsSent();
        } catch (Throwable $e) {
            if ($this->retryPolicy->shouldRetry($email->getAttempts())) {
                $nextAttemptAt = $this->retryPolicy->calculateNextAttemptAt(
                    attempts: $email->getAttempts(),
                    now: $this->clock->now()
                );
                $email->markAsFailed(error: $e->getMessage(), nextAttemptAt: $nextAttemptAt);
            } else {
                $this->logger->critical('Email sending failed permanently', [
                    'id' => $email->getId()?->value(),
                    'trace_id' => $email->getTraceId()?->value(),
                    'error' => $e->getMessage(),
                ]);

                $email->markAsFailedPermanently(error: $e->getMessage());
            }
        } finally {
            $this->writeRepository->save($email);
        }
    }
}
