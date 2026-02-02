<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Mailer;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

readonly class SmtpMailer implements MailerInterface
{
    public function __construct(
        private SymfonyMailerInterface $symfonyMailer,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(OutboxEmail $email): ?string
    {
        $mimeEmail = new Email()
            ->from(new Address(address: $email->getFrom()->value(), name: $email->getFromName()->value()))
            ->to($email->getTo()->value())
            ->subject($email->getSubject()->value())
            ->html($email->getBody()->value());

        $this->symfonyMailer->send($mimeEmail);

        return null;
    }

    public static function getDefaultIndexName(): string
    {
        return DriverEnum::Smtp->value;
    }
}
