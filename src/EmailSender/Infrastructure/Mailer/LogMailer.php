<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Mailer;

use App\EmailSender\Domain\Entity\OutboxEmail;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

readonly class LogMailer implements MailerInterface
{
    public function __construct(
        #[Target('emailSenderLogger')]
        private LoggerInterface $logger,
    ) {
    }

    public function send(OutboxEmail $email): ?string
    {
        $this->logger->info('Simulating email sending', [
            'id' => $email->getId()?->value(),
            'to' => $email->getTo()->value(),
            'subject' => $email->getSubject()->value(),
            'trace_id' => $email->getTraceId()?->value(),
        ]);

        return null;
    }
}
