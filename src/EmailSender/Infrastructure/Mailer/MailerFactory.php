<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Mailer;

use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class MailerFactory
{
    /** @var iterable<MailerInterface> */
    private iterable $mailers;

    /**
     * @param iterable<MailerInterface> $mailers
     */
    public function __construct(
        #[AutowireIterator('app.email_driver')] iterable $mailers,
    ) {
        $this->mailers = $mailers;
    }

    public function make(DriverEnum $driver): MailerInterface
    {
        foreach ($this->mailers as $mailer) {
            if (DriverEnum::Log === $driver && $mailer instanceof LogMailer) {
                return $mailer;
            }
            if (DriverEnum::Smtp === $driver && $mailer instanceof SmtpMailer) {
                return $mailer;
            }
        }

        throw new \RuntimeException(sprintf('Mailer driver "%s" not found', $driver->value));
    }
}
